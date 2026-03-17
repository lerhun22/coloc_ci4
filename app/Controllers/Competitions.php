<?php

namespace App\Controllers;

use App\Models\CompetitionModel;
use App\Models\PhotoModel;

class Competitions extends BaseController
{
    protected $photoModel;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
    }

    public function index()
    {
        $model = new \App\Models\CompetitionModel();
        $data = $this->data;
        $data['competitions_list'] = $model->getCompetitionsWithCount();

        return view('competitions/index', $data);
    }

    public function show($id)
    {
        $model = new CompetitionModel();

        $competition = $model->getCompetitionStats($id);

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        session()->set('competition_id', $id);

        return redirect()->to(site_url('competitions/' . $id . '/photos'));
    }

    public function photos($id)
    {
        session()->set('competition_id', $id);

        $competitionModel = new \App\Models\CompetitionModel();

        $competition = $competitionModel->getCompetitionStats($id);

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();

        $photos = $db->table('photos p')
            ->select("
            p.id,
            p.ean,
            p.titre,
            p.saisie,
            p.place,
            pa.nom AS auteur,
            cl.nom AS club
        ")
            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs cl', 'cl.id = pa.clubs_id', 'left')
            ->where('p.competitions_id', $id)
            ->orderBy('p.place', 'ASC')
            ->orderBy('p.saisie', 'ASC')
            ->get()
            ->getResultArray();

        return view('competitions/photos', [
            'competition' => $competition,
            'photos' => $photos
        ]);
    }


    public function notation($competition_id)
    {
        $competitionModel = new CompetitionModel();

        $competition = $competitionModel->find($competition_id);

        $data = [
            'competition' => $competition
        ];

        return view('competitions/notation', $data);
    }

    public function scan($competitionId)
    {
        $barcode = $this->request->getPost('barcode');

        $photo = $this->photoModel
            ->where('barcode', $barcode)
            ->first();

        return view('competitions/notation', [
            'photo' => $photo,
            'competitionId' => $competitionId
        ]);
    }

    public function saveNotes($competitionId)
    {
        $photoId = $this->request->getPost('photo_id');

        $j1 = $this->request->getPost('judge1');
        $j2 = $this->request->getPost('judge2');
        $j3 = $this->request->getPost('judge3');

        $total = $j1 + $j2 + $j3;

        $this->photoModel->update($photoId, [
            'judge1' => $j1,
            'judge2' => $j2,
            'judge3' => $j3,
            'total' => $total
        ]);

        return redirect()->back();
    }
}