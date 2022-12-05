<?php
// Листинг 3.39

class ConfReader
{
    public function getValues(array $default = null)
    {
        $values = [];

        // Выполнить действия для получения новых значений
        // Добавить переданные значения (результат всегда является массивом)

        $values = array_merge($default, $values);
        return $values;
    }
}