<?php

namespace App\Libraries;

class CompetitionService
{
    public static function setActive($id)
    {
        session()->set('competition_id', $id);
    }

    public static function getActive()
    {
        return session()->get('competition_id');
    }

    public static function clear()
    {
        session()->remove('competition_id');
    }
}
