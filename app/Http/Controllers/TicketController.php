<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Hall;
use App\Models\HallConfig;
use App\Models\Seance;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /***
     * Добавление билета в БД и обновление конфигурации схемы зала (с учетом купленных билетов)
     * @param Request $request
     * @return array - возвращает массив купленных булетов
     */
    public function ticketAdd(Request $request) {
        try {
            // Проверяем целостность входных данных
            if (!$request->has(['ticketDate', 'seanceId', 'tickets']))
                throw new Exception('Отсутствуют некоторые обязательные параметры');
            // Находим сеанс
            $seance = Seance::find($request->seanceId);
            if (!$seance)
                throw new Exception('Id сеанса указан не верно (сеанс с таким id не найден)');
            // Находим фильм и зал
            $film = Film::find($seance->seance_filmid);
            $hall = Hall::find($seance->seance_hallid);
            if (!$film || !$hall)
                throw new Exception('Фильм или(и) зал с таким id не найден(ы)');
            // Распарсим строку в массив с билетами выбранными пользователями
            $arrayTickets = json_decode($request->tickets);
            if (!is_array($arrayTickets))
                throw new Exception('Билеты (tickets) переданы в неверном формате');

            // Проверяем целостность данных и формируем билеты
            $ticketsOK = [];
            foreach ($arrayTickets as $tick) {

                if (!isset($tick->row, $tick->place, $tick->coast))
                    throw new Exception('Один из билетов передан в не верном формате');

                if (($tick->row > $hall->hall_rows) || ($tick->place > $hall->hall_places))
                    throw new Exception('Такого места в этом зале нет');
                $ticket = new Ticket();
                $ticket->ticket_date = $request->ticketDate;
                $ticket->ticket_time = $seance->seance_time;
                $ticket->ticket_filmname = $film->film_name;
                $ticket->ticket_hallname = $hall->hall_name;
                $ticket->ticket_row = $tick->row;
                $ticket->ticket_place = $tick->place;
                $ticket->ticket_price = $tick->coast;
                $ticketsOK[] = $ticket;
                // $ticket->save();
            }
            // Получаем схему посадочных мест на этот сенас
            $timestamp = strtotime($request->ticketDate . ' ' . $seance->seance_time);
            $seanceId = $request->seanceId;
            $hallConfigSeance = $this->getHallConfigSeance($seanceId, $timestamp);
            // Делаем новую схему
            $newConfiguration = $this->addTicketsInHallConfig($hallConfigSeance->hallconfigs_configuration, $ticketsOK);
            // Записываем билеты в БД
            foreach ($ticketsOK as $tick) {
                $tick->save();
            }
            // Актуализируем схему в БД
            $hallConfigSeance->hallconfigs_configuration = $newConfiguration;
            $hallConfigSeance->update();

            // Возвращаем массив с купленными билетами.
            return [
                "success" => true,
                "result" => $ticketsOK,
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    /**
     * Обновление схемы зала согласно купленным билетам (отмечаем на схеме места, которые указаны в билетах, как занятые)
     * @param $configStr - схема зала в строковом формате
     * @param $arrTickets - массив с билетами
     * @return false|string - обновленная схема зала
     * @throws Exception - ошибка если такие места уже заняты
     */
    private function addTicketsInHallConfig($configStr, $arrTickets) {
        $configArr = json_decode($configStr);
        foreach ($arrTickets as $tick) {
            if (($configArr[$tick->ticket_row-1][$tick->ticket_place-1] !== 'standart') &&
                ($configArr[$tick->ticket_row-1][$tick->ticket_place-1] !== 'vip') )
                throw new Exception('Не возможно забронировать место (ряд ' . $tick->ticket_row . ' место ' . $tick->ticket_place  . ')');
            $configArr[$tick->ticket_row-1][$tick->ticket_place-1] = 'taken';
        }
        return json_encode($configArr);
    }

    /**
     * Возвращает схему зала на конкретный сеанс привязанный к дате и времени. Если такой записи нет, то она создается
     * @param $seanceId - ID сеанса
     * @param $timestamp - timestamp с учетом времени начала сеанса и даты
     * @return HallConfig - запись из БД (найденная или созданная)
     */
    private function getHallConfigSeance($seanceId, $timestamp) {
        $hallConfigItem = DB::table('hallconfigs')
            ->where('hallconfigs_seanceid', '=', $seanceId)
            ->where('hallconfigs_timestamp', '=', $timestamp)
            ->get();
        if (count($hallConfigItem) === 0) { // Если ничего не нашлось, создаем новую запись
            $seance = Seance::find($seanceId); // Находим сеанс
            $hall = Hall::find($seance->seance_hallid); // Находим зал чтобы потом вытащить схему пустого зала
            $hallConfig = new HallConfig();
            $hallConfig->hallconfigs_seanceid = $seanceId;
            $hallConfig->hallconfigs_timestamp = $timestamp;
            $hallConfig->hallconfigs_configuration = $hall->hall_config;
            $hallConfig->save();
            return $hallConfig;
        }

        return $hallConfigItem[0];
    }
}
