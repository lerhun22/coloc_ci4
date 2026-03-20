<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\CompetitionModel;
use App\Libraries\CompetitionService;

abstract class BaseController extends Controller
{
    protected $data = [];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $model = new CompetitionModel();


        /*
        ==========================
        Liste compétitions
        ==========================
        */

        $this->data['competitions'] =
            $model->getCompetitionsWithCount();

        $this->data['competitions_list'] =
            $this->data['competitions'];


        /*
        ==========================
        Compétition active ID
        ==========================
        */

        $competitionId =
            CompetitionService::getActive();

        $this->data['competitionId'] =
            $competitionId;


        /*
        ==========================
        Compétition active complète
        ==========================
        */

        $activeCompetition = null;

        if ($competitionId) {

            foreach ($this->data['competitions'] as $c) {

                if ($c['id'] == $competitionId) {

                    $activeCompetition = $c;
                    break;
                }
            }
        }

        $this->data['activeCompetition'] =
            $activeCompetition;
    }
}