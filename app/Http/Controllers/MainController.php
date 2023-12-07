<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Hall;
use App\Models\Seance;
use Exception;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class MainController extends Controller {

    /**
     * Возвращает все залы фильмы и сеансы
     * @return array
     */
    public function getData() {
        try {
            $result = array(
                "halls" => (new HallController)->getHalls(),
                "films"  => Film::all(),
                "seances" => SeanceController::getSeances()
            );
            return [
                "success" => true,
                "result" => $result
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function getHallConfig(Request $request) {
        try {
            // Проверям целостность входных данных
            if (!$request->has(['seanceId', 'date']) ||
                (!$request->seanceId) || (!$request->date))
                throw new Exception('Отсутствуют некоторые обязательные параметры');
            $seanceId = $request->seanceId;
            $seance = Seance::find($seanceId);
            if (!$seance)
                throw new Exception('Сеанс с таким id не найден');
            $timestamp = strtotime($request->date . ' ' . $seance->seance_time);

            $hallConfigItem = DB::table('hallconfigs')
                ->where('hallconfigs_seanceid', '=', $seanceId)
                ->where('hallconfigs_timestamp', '=', $timestamp)
                ->get();
            if (count($hallConfigItem) === 0) { // Если ничего не нашлось, создаем новую запись
                $hall = Hall::find($seance->seance_hallid); // Находим зал чтобы потом вытащить схему пустого зала
                $result = $hall->hall_config;
            } else {
                $result = $hallConfigItem[0]->hallconfigs_configuration;
            }
            return [
                "success" => true,
                "result" => json_decode($result)
            ];

        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function login(Request $request)  {

        try {
            // Проверям целостность входных данных
            if (!$request->has(['login', 'password']) ||
               ($request->login !== "shfe-diplom@netology.ru" ) || ($request->password !== "shfe-diplom" ))
                throw new Exception('Ошибка авторизации - указаны не верные данные (логин и(или) пароль)');
            return [
                "success" => true,
                "result" => "Авторизация пройдена успешно!"
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }






   /* public function home() {
        return view('home');
    } */

}
