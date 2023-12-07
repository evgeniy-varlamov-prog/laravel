<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Seance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FilmController extends Controller
{
    /**
     * Добавление нового фильма в БД
     * @param Request $request тело запроса (Данные которые мы добавляем в таблицу)
     * @return array Добавляет новую запись в таблицу с сеансами и возвращает список сеансов
     */
    public function filmAdd(Request $request) {
        try {
            // Проверяем целостность входных данных
            if (!$request->has(['filmName', 'filmDuration', 'filmDescription', 'filmOrigin', 'filePoster']))
                throw new Exception('Отсутствуют некоторые обязательные параметры');
            // Проверяем файл с изображением
            $file = $request->file('filePoster');
            if (!$file || ($file->getMimeType() !== 'image/png') || ($file->getSize() > 3145728) )
                throw new Exception('Ошибка загрузки файла - файл должен быть в формате png и не больше 3 мб');

            // Создаем новый элемент
            $film = new Film();
            $film->film_name = $request->filmName;
            $film->film_duration = $request->filmDuration;
            $film->film_description = $request->filmDescription;
            $film->film_origin = $request->filmOrigin;
            $film->film_poster = $file->store('img/posters');

            // Раболтаем с БД
            $film->save();
            $result = array(
                "films" => $film->all(),
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
     * Удаляет запись из таблицы с фильмами
     * @param $id - id удаляемого фильма
     * @return array Массив с фильмами и массив с сеансами
     */
    public function filmDell($id) {
        // Работаем с БД
        try {
            $film = Film::findOrFail($id);
            $fileName = $film->film_poster;
            $film->delete(); // Удаляем запись из БД
            Storage::delete($fileName); // Удаляем файл с постером
            $result = array(
                "films" => $film->all(),
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
}
