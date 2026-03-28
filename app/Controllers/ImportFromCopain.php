<?php

namespace App\Controllers;

class ImportFromCopain extends BaseController
{
    /**
     * =========================
     * LISTE DES COMPÉTITIONS
     * =========================
     */
    public function index()
    {
        // =========================
        // CONFIG (.env)
        // =========================
        $email = env('copain.email');
        $password = env('copain.password');
        $profile = env('copain.profile', 'national');
        $userUr = env('copain.ur', '22');

        if (!$email || !$password) {
            log_message('error', 'EMAIL OU PASSWORD MANQUANT (.env)');
            return view('import/index', ['competitions' => []]);
        }

        $cookie = WRITEPATH . 'copain_cookie.txt';

        // 🔥 reset cookie AVANT login
        if (file_exists($cookie)) {
            unlink($cookie);
        }

        // =========================
        // LOGIN + SESSION
        // =========================
        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions($email, $password);

        if (($login['code'] ?? 1) != 0) {
            log_message('error', 'LOGIN ERROR');
            return view('import/index', ['competitions' => []]);
        }

        // =========================
        // DATA COPAIN
        // =========================
        $rawRegional = $login['rcompetitions'] ?? [];
        $rawNational = $login['competitions'] ?? [];

        // 🔥 fusion
        $raw = array_merge($rawNational, $rawRegional);

        $competitions = [];
        $rcompetitions = [];

        foreach ($raw as $c) {

            $id = $c['id'] ?? null;
            if (!$id) continue;

            $ur = $c['urs_id']
                ?? $c['ur']
                ?? $c['urs']
                ?? '';

            $isNational = empty($ur);

            // 🔥 FILTRAGE PROFIL
            if ($profile === 'regional') {
                if ($isNational) continue; // cache nationales
                if ($userUr && $ur != $userUr) continue; // autre UR
            }

            if ($isNational) {
                $label = 'National';
                $typeCode = 'N';
            } else {
                $label = 'Régional';
                $typeCode = 'R';
            }

            $competitions[] = [
                'id'        => $id,
                'nom'       => $c['nom'] ?? '',
                'saison'    => $c['saison'] ?? '',
                'urs_id'    => $ur,
                'label'     => $label,
                'type_code' => $typeCode,
                'folder'    => $c['folder'] ?? ''
            ];
        }


        log_message('debug', 'COMPETITIONS COUNT=' . count($competitions));

        return view('import/index', [
            'competitions' => $competitions
        ]);
    }


    /**
     * =========================
     * IMPORT D’UNE COMPÉTITION
     * =========================
     */
    public function start($id)
    {


        $type = $this->request->getGet('type');
        $folder = $this->request->getGet('folder');

        session()->set('import', [
            'id' => $id,
            'type' => $type,
            'folder' => $folder,
            'started' => false,
            'done' => false
        ]);
        /*
        return view('import/progress', [
            'id' => $id
        ]);
*/
        return $this->response->setJSON([
            'status' => 'ok'
        ]);
    }

    public function step($id)
    {

        $session = session();
        $data = $session->get('import');

        if (!$data || $data['id'] != $id) {
            return $this->response->setJSON([
                'status' => 'error',
                'step' => 'Session perdue',
                'progress' => 0
            ]);
        }

        if (empty($data['done'])) {

            $importer = new \App\Libraries\CopainImporter();

            $importer->importCompetition(
                $data['id'],
                $data['type'],
                $data['folder'],
                function ($step, $percent) use ($session, $data) {

                    $data['current_step'] = $step;
                    $data['progress'] = $percent;

                    $session->set('import', $data);
                }
            );

            $data['done'] = true;
            $session->set('import', $data);
        }

        return $this->response->setJSON([
            'status' => $data['done'] ? 'done' : 'running',
            'step' => $data['current_step'] ?? '...',
            'progress' => $data['progress'] ?? 0
        ]);
    }

    /**
     * =========================
     * FAIL HELPER
     * =========================
     */
    protected function fail($message)
    {
        return redirect()->back()->with('error', $message);
    }
}