<?php

namespace App\Controllers;

use App\Models\PhotoModel;
use App\Models\JugementModel;
use App\Models\CompetitionModel;
use App\Libraries\CompetitionService;

class Jugement extends BaseController
{
    protected $photoModel;
    protected $jugementModel;
    protected $competitionModel;
    protected $db;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
        $this->jugementModel = new JugementModel();
        $this->competitionModel = new CompetitionModel();

        $this->db = \Config\Database::connect();
    }


    /* =====================================================
       PAGE PRINCIPALE
    ===================================================== */

    public function index()
    {

        /*
        =====================
        COMPETITION ACTIVE
        =====================
        */

        $competition = CompetitionService::getActive();

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $competition_id = $competition['id'];


        /*
        =====================
        DOSSIER COMPETITION
        =====================
        */

        $competitionFolder =
            $competition['saison'] . '_' .
            $competition['urs_id'] . '_' .
            $competition['numero'] . '_' .
            $competition['id'];


        /*
        =====================
        JUGES
        =====================
        */

        $juges = $this->db->table('juges')
            ->where('competitions_id', $competition_id)
            ->get()
            ->getResultArray();

        $nb_juges = count($juges);


        /*
        =====================
        PHOTOS + NB NOTES
        =====================
        */

        $photos = $this->db->table('photos p')

            ->select('
                p.*,
                pa.nom,
                pa.prenom,
                c.nom as club,
                COUNT(n.photos_id) as nb_notes
            ')

            ->join('participants pa', 'pa.id = p.participants_id', 'left')

            ->join('clubs c', 'c.id = pa.clubs_id', 'left')

            ->join(
                'notes n',
                'n.photos_id = p.id
                 AND n.competitions_id = ' . $competition_id,
                'left'
            )

            ->where('p.competitions_id', $competition_id)

            ->groupBy('p.id')

            ->orderBy('p.passage', 'ASC')

            ->get()

            ->getResultArray();


        if (empty($photos)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }


        /*
        =====================
        PHOTO COURANTE
        =====================
        */

        $photo = $photos[0];

        $photo['auteur'] =
            ($photo['prenom'] ?? '') . ' ' .
            ($photo['nom'] ?? '');

        $position = 1;
        $total = count($photos);


        /*
        =====================
        PATH PHOTOS
        =====================
        */

        $photosPath =
            './public/uploads/competitions/' .
            $competitionFolder .
            '/photos';


        /*
        =====================
        VIEW
        =====================
        */

        return view('jugement/index', [

            'competition' => $competition,
            'competition_id' => $competition_id,

            'photos' => $photos,
            'photo' => $photo,

            'juges' => $juges,
            'nb_juges' => $nb_juges,

            'competitionFolder' => $competitionFolder,
            'photosPath' => $photosPath,

            'position' => $position,
            'total' => $total

        ]);
    }



    /* =====================================================
       PHOTO + NOTES
    ===================================================== */

    public function photo($competition_id = null, $photo_id = null)
    {

        if (!$competition_id || !$photo_id) {
            return $this->response->setStatusCode(404);
        }

        $builder = $this->db->table('photos');

        $builder->select('
        photos.*,
        participants.id as participant_id,
        clubs.nom as club
    ');

        $builder->join(
            'participants',
            'participants.id = photos.participants_id',
            'left'
        );

        $builder->join(
            'clubs',
            'clubs.id = participants.clubs_id',
            'left'
        );

        $builder->where('photos.id', $photo_id);

        $photo = $builder->get()->getRowArray();

        if (!$photo) {
            return $this->response->setStatusCode(404);
        }

        $notes = $this->jugementModel->getNotes(
            $photo_id,
            $competition_id
        );

        return $this->response->setJSON([
            'photo' => $photo,
            'notes' => $notes
        ]);
    }



    /* =====================================================
       SAVE NOTE
    ===================================================== */

    public function saveNote($competition_id = null)
    {

        $photo_id = $this->request->getPost('photo_id');
        $juge_id = $this->request->getPost('juge');
        $note = $this->request->getPost('note');


        $exists = $this->db->table('notes')

            ->where('photos_id', $photo_id)

            ->where('juges_id', $juge_id)

            ->where('competitions_id', $competition_id)

            ->countAllResults();


        if ($exists) {

            $this->db->table('notes')

                ->where('photos_id', $photo_id)

                ->where('juges_id', $juge_id)

                ->where('competitions_id', $competition_id)

                ->update([
                    'note' => $note
                ]);
        } else {

            $this->db->table('notes')

                ->insert([
                    'photos_id' => $photo_id,
                    'juges_id' => $juge_id,
                    'competitions_id' => $competition_id,
                    'note' => $note
                ]);
        }


        $total = $this->db->table('notes')

            ->selectSum('note')

            ->where('photos_id', $photo_id)

            ->where('competitions_id', $competition_id)

            ->get()

            ->getRow()

            ->note ?? 0;


        $this->db->table('photos')

            ->where('id', $photo_id)

            ->update([
                'note_totale' => $total
            ]);


        return $this->response->setJSON([
            'ok' => true,
            'total' => $total
        ]);
    }

    /* =====================================================
   DISQUALIFY
===================================================== */

    public function disqualify($competition_id = null, $photo_id = null)
    {
        if (!$photo_id) {
            return $this->response->setStatusCode(404);
        }

        $photo = $this->db->table('photos')
            ->where('id', $photo_id)
            ->get()
            ->getRowArray();

        if (!$photo) {
            return $this->response->setStatusCode(404);
        }

        $new = ($photo['disqualifie'] ?? 0) ? 0 : 1;

        $this->db->table('photos')
            ->where('id', $photo_id)
            ->update([
                'disqualifie' => $new
            ]);

        return $this->response->setJSON([
            'ok' => true,
            'state' => $new ? 'disq' : 'pending'
        ]);
    }
}
