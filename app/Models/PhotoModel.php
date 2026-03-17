<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoModel extends Model
{
    protected $table = 'photos';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'competition_id',
        'barcode',
        'file',
        'title',
        'author',
        'judge1',
        'judge2',
        'judge3',
        'total',
        'medailles_id'
    ];

}