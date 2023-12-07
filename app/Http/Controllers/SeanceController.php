<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Hall;
use App\Models\Seance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeanceController extends Controller
{
    /**
     * Добавление нового сеанса в БД
     * @param Request $request тело запроса (Данные которые мы добавляем в таблицу)
     * @return array Добавляет новую запись в таблицу с сеансами и возвращает список сеансов
     */
    public function seanceAdd(Request $request) {
        try {
            // Проверяем целостность входных данных
            if (!$request->has(['seanceHallid', 'seanceFilmid', 'seanceTime']))
                throw new Exception('Отсутствуют некоторые обязательные параметры');

            // Проверяем есть ли в базе зал и фильм с такими id
            $film = Film::find($request->seanceFilmid);
            $hall = Hall::find($request->seanceHallid);
            if (!$film || !$hall)
                throw new Exception('Фильм или(и) зал с таким id не найден(ы)');

            // Вычисляем timestamp начала и конца сеанса (в минутах) относительно начала суток
            $start = (int)  (strtotime($request->seanceTime) - strtotime('today'))/60; // Начало сеанса мин от начала суток
            $end = (int) $start + $film->film_duration; // конец сеанса
            // Получаем список всех сеансов и проверяем пересечения по времени с вновьдобовляемым сеансом
            $seances = Seance::all();
            global $seanceOverly;
            $seanceOverly = false; // Пересечения нет
            foreach ($seances as $seance) {
                if (($seance->seance_hallid == $request->seanceHallid) && ((($start >= $seance->seance_start) &&
                            ($start <=  $seance->seance_end)) || (($end >= $seance->seance_start) &&
                            ($end <= $seance->seance_end)) || ($end >= 1440))) {
                    $seanceOverly = true; // Найдено пересечение по времени с другими сеансами
                    break;
                }
            }
            if ($seanceOverly)
                throw new Exception('Сеанс пересекается по времени с другими сеансами');

            // Создаем новый элемент
            $seance = new Seance();
            $seance->seance_hallid = $request->seanceHallid;
            $seance->seance_filmid = $request->seanceFilmid;
            $seance->seance_time = $request->seanceTime;
            $seance->seance_start = $start;
            $seance->seance_end = $end;

            $seance->save();
            $result = array(
                "seances" => $this->getSeances(),
            );
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
        // Возвращаем результат
        return [
            "success" => true,
            "result" => $result,
        ];
    }

    /**
     * Удаляет запись из таблицы с сеансами
     * @param $id - id удаляемого зала
     * @return array Массив с сеансами
     */
    public function seanceDell($id) {
        // Работаем с БД
        try {
            $seance = Seance::findOrFail($id);
            $seance->delete();
            $result = array(
                "seances" => $this->getSeances()
            );
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
        // Возвращаем результат
        return [
            "success" => true,
            "result" => $result,
        ];
    }

    static function getSeances () {
        // Работаем с БД
        try {
            $result = array(
                "seances" => DB::table('seances')->select('id', 'seance_hallid', 'seance_filmid', 'seance_time')->get()
            );
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
        // Возвращаем результат
        return [
            "success" => true,
            "result" => $result,
        ];
    }
}




