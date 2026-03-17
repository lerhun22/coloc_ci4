<?php

namespace App\Models;

use CodeIgniter\Model;

class JugementModel extends Model
{

    protected $table = 'notes';

    protected $allowedFields = [
        'photos_id',
        'juges_id',
        'competitions_id',
        'note'
    ];


    /* ===============================
       PHOTO + NOTES
    =============================== */

    public function getPhotoWithNotes($photo_id, $competition_id)
    {
        $db = \Config\Database::connect();


        /*
    ======================
    LISTE PHOTOS (pour position)
    ======================
    */

        $list = $db->table('photos')

            ->select('id')

            ->where('competitions_id', $competition_id)

            ->orderBy('saisie', 'ASC')

            ->get()

            ->getResultArray();


        $total = count($list);

        $position = 1;

        foreach ($list as $i => $p) {

            if ($p['id'] == $photo_id) {

                $position = $i + 1;
                break;
            }
        }


        /*
    ======================
    PHOTO + AUTEUR + CLUB
    ======================
    */

        $photo = $db->table('photos p')

            ->select('
            p.*,
            pa.nom,
            pa.prenom,
            c.nom as club
        ')

            ->join('participants pa', 'pa.id = p.participants_id', 'left')

            ->join('clubs c', 'c.id = pa.clubs_id', 'left')

            ->where('p.id', $photo_id)

            ->get()

            ->getRowArray();


        if (!$photo) {

            return [
                'photo' => null,
                'notes' => [],
                'position' => 0,
                'total' => 0,
                'disqualified' => false
            ];
        }


        $photo['auteur'] =
            trim(($photo['prenom'] ?? '') . ' ' . ($photo['nom'] ?? ''));


        $disqualified = !empty($photo['disqualifie']);


        /*
    ======================
    NOTES
    ======================
    */

        $notes = $db->table('notes')

            ->select('juges_id, note')

            ->where('photos_id', $photo_id)
            ->where('competitions_id', $competition_id)

            ->get()

            ->getResultArray();


        return [

            'photo' => $photo,
            'notes' => $notes,

            'position' => $position,
            'total' => $total,

            'disqualified' => $disqualified

        ];
    }

    /* ===============================
       SAVE NOTE
    =============================== */

    public function saveNote(
        $photo_id,
        $juge_id,
        $note,
        $competition_id
    ) {

        $exists = $this->db->table('notes')
            ->where('photos_id', $photo_id)
            ->where('juges_id', $juge_id)
            ->countAllResults();


        if ($exists) {

            $this->db->table('notes')
                ->where('photos_id', $photo_id)
                ->where('juges_id', $juge_id)
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
    }

    public function getNotes($photo_id, $competition_id)
    {
        return $this->db->table('notes')
            ->where('photos_id', $photo_id)
            ->where('competitions_id', $competition_id)
            ->get()
            ->getResultArray();
    }
}