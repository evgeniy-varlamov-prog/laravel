<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Seance;
use Exception;
use Illuminate\Http\Request;


class HallController extends Controller
{
    /**
     * Добавление нового зала в БД
     * @param Request $request тело запроса (Данные которые мы добавляем в таблицу)
     * @return array Добавляет новую запись в таблицу с сеансами и возвращает список сеансов
     */
    public function hallAdd(Request $request) {
        try {
            // Проверям целостность входных данных
            if (!$request->has(['hallName']) || !$request->hallName)
                throw new Exception('Отсутствуют некоторые обязательные параметры');
            // Создаем новый элемент
            $hall = new Hall();
            $hall->hall_name = $request->hallName;
            $hall->hall_rows = 10;
            $hall->hall_places = 10;
            $hall->hall_config = json_encode(HALL_CONFIGURATION_STANDART);
            $hall->hall_price_standart = 100;
            $hall->hall_price_vip = 350;
            $hall->hall_open = 0;

            // Раболтаем с БД
            $hall->save();
            $result = array(
                "halls" => $this->getHalls(), //$hall->all(),
            );
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => ($e->getCode() == 23000) ? "Возможно зал с таким названием уже существует" : $e->getMessage()
            ];
        }
        // Возвращаем результат
        return [
            "success" => true,
            "result" => $result,
        ];
    }

    /**
     * Удаляет запись из таблицы с залами
     * @param $id - id удаляемого зала
     * @return array Массив с залами и массив с сеансами
     */
    public function hallDel($id) {
        // Работаем с БД
        try {
            $hall = Hall::findOrFail($id);
            $hall->delete();
            $result = array(
                "halls" => $this->getHalls(), //$hall->all(),
                "seances" => SeanceController::getSeances()
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
     * Изменяет конфигурацию посадочных мест в зале
     * @param Request $request - тело запроса (новая схема зала)
     * @param $id - id зала конфигуоацию которого нужно изменять
     * @return array - возвращает изменяемый элемент (зал)
     */
    public function hallConfig(Request $request, $id) {
        try {
            // Проверям целостность входных данных
            if (($request->rowCount == null) ||
                ($request->placeCount == null) ||
                ($request->config == null))
                throw new Exception('Отсутствуют некоторые обязательные параметры');

            // Работаем с БД
            $hall = Hall::findOrFail($id);
            // Вносим изменения в запись
            $hall->hall_rows = $request->rowCount;
            $hall->hall_places = $request->placeCount;
            if (!$this->hallConfigIsCorrect($request->rowCount, $request->placeCount, $request->config)) {
                throw new Exception('Конфигурация зала не корректная');
            }
            $hall->hall_config = $request->config;
            $hall->update();
            $result = $hall;
            $result->hall_config = json_decode($result->hall_config);
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
     * Изменяет стоимость билетов в зале
     * @param Request $request - тело запроса (новые цены)
     * @param $id - id зала стоимость билетов в котором нужно изменять
     * @return array - возвращает изменяемый элемент (зал)
     */
    public function setPrice(Request $request, $id)  {
        try {
            // Проверям целостность входных данных
            if (!$request->has(['priceStandart', 'priceVip']) ||
               (!$request->priceStandart) || (!$request->priceVip))
                throw new Exception('Отсутствуют некоторые обязательные параметры');
            // Работаем с БД
            $hall = Hall::findOrFail($id);
            // Вносим изменения в запись
            $hall->hall_price_standart = $request->priceStandart;
            $hall->hall_price_vip = $request->priceVip;
            $hall->update();
            $result = $hall;
            $result->hall_config = json_decode($result->hall_config);
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
     * Открыть/закрыть продажи в зале
     * @param Request $request - тело запроса (новые цены)
     * @param $id - id зала стоимость билетов в котором нужно изменять
     * @return array - возвращает изменяемый элемент (зал)
     */
    public function OpenHall(Request $request, $id)  {
        // Проверям целостность входных данных
        if (($request->hallOpen != 0) &&
            ($request->hallOpen != 1) ) {
            return [
                "success" => false,
                "result" => 'Отсутствуют или не верно заданы нектороые обязательные параметры'
            ];
        }
        // Работаем с БД
        try {
            $hall = Hall::findOrFail($id);
            // Вносим изменения в запись
            $hall->hall_open = $request->hallOpen;
            $hall->update();
            $result = array(
                "halls" => $this->getHalls(),
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
     * Функция возвращает список залов (строка с конфигурацией зала преобразуется при этом в массив)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHalls() {
        $halls = Hall::all();
        foreach ($halls as $hal) {
            $hal->hall_config = json_decode($hal->hall_config);
        }
        return $halls;
    }

    /**
     * Проверяет правильность заполения массива с конфигурацией посадочных мест
     * - Кол-во элементов в основном массиве должно быть равно кол-ву рядов ($rowCount)
     * - Кол-во элементов в подмассиве должно быть равно кол-ву мест ($placesCount)
     * - В подмассиве допускаются значения  standart, vip, disabled
     * @param $rowCount - кол-во рядов
     * @param $placesCount - кол-во мест в ряду
     * @param $hallConfigStr - строка с конфигурацией зала
     * @return bool - корректность схемы зала
     */
    public function hallConfigIsCorrect($rowCount, $placesCount, $hallConfigStr) {
        $hallConfig = json_decode($hallConfigStr);
        if (count($hallConfig) !== (int) $rowCount) return false;
        foreach ($hallConfig as $row) {
            if (count($row) !== (int) $placesCount) return false;
            foreach ($row as $place) {
                if (($place !== 'standart') && ($place !== 'vip') && ($place !== 'disabled')) return false;
            }
        }
        return true;
    }
}


const HALL_CONFIGURATION_STANDART  = [
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
    ['standart', 'standart','standart','standart','standart','standart','standart','standart','standart','standart'],
];

//const HALL_CONFIGURATION_STANDART = '<div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__row"><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span><span class="conf-step__chair conf-step__chair_standart"></span></div><div class="conf-step__hall-wrapper__save-status"></div>';
