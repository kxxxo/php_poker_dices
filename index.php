<?php
//$row = [4,3,1,3,5];
//$hand = new Hand($row);
$hand = new Hand();
echo 'Рука: ' . implode(',', $hand->getRow()) . PHP_EOL;
echo $hand->getHandTypeName() . PHP_EOL;

class Hand
{
    const ROW_LENGTH = 5;
    const MIN_NOMINAL = 1;
    const MAX_NOMINAL = 6;

    /** @var array $_row */
    private $_row = [];

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->_row;
    }

    /***
     * @return int
     * @see HandType
     */
    public function getHandTypeId()
    {
        $row = $this->_row; // clone
        sort($row);

        if ($this->_isPoker($row)) {
            return HandType::POKER;
        } elseif ($this->_isQuad($row)) {
            return HandType::QUAD;
        } elseif ($this->_isFullHouse($row)) {
            return HandType::FULL_HOUSE;
        } elseif ($this->_isBigStraight($row)) {
            return HandType::BIG_STRAIGHT;
        } elseif ($this->_isLilStraight($row)) {
            return HandType::LIL_STRAIGHT;
        } elseif ($this->_isTrips($row)) {
            return HandType::TRIPS;
        } elseif ($this->_isTwoPair($row)) {
            return HandType::TWO_PAIR;
        } elseif ($this->_isPair($row)) {
            return HandType::PAIR;
        } else {
            return HandType::CHANCE;
        }

    }

    /**
     * @return string
     */
    public function getHandTypeName()
    {
        return HandType::getName($this->getHandTypeId());
    }

    /**
     * Hand constructor.
     * @param array|null $row
     * @throws Exception
     */
    public function __construct(array $row = null)
    {
        if ($row === null) {
            $this->_row = $this->_generateRow();
        } else {
            $this->_validateRow($row);
            $this->_row = $row;
        }
    }

    /**
     * @param $row
     * @throws Exception
     */
    private function _validateRow($row)
    {
        if (count($this->_row) > self::ROW_LENGTH) {
            throw new Exception("Incorrect row size");
        }
        array_walk($row, function (&$item, $key) {
            if (!is_int($item)) {
                throw new Exception("Incorrect row value type on $key position");
            }
            if ($item < self::MIN_NOMINAL || $item > self::MAX_NOMINAL) {
                throw new Exception("Incorrect row value on $key position");
            }
        });

    }

    /**
     * @return array
     */
    private function _generateRow()
    {
        $row = [];
        for ($i = 0; $i < self::ROW_LENGTH; $i++) {
            $row[] = rand(self::MIN_NOMINAL, self::MAX_NOMINAL);
        }
        return $row;
    }


    /**
     * Две кости одинакового достоинства
     * @param $row
     * @return bool
     */
    private function _isPair($row)
    {
        return count(array_unique($row)) !== count($row);
    }

    /**
     * Две кости одного достоинства и две кости другого достоинства
     * @param $row
     * @return bool
     */
    private function _isTwoPair($row)
    {
        if (
            $this->_isUniqueRow(array_slice($row, 0, 2))
            && (
                $this->_isUniqueRow(array_slice($row, 2, 2))
                || $this->_isUniqueRow(array_slice($row, 3, 2))
            )) {
            // case for xx-yy-z AND xx-z-yy
            return true;
        } elseif (
            $this->_isUniqueRow(array_slice($row, 1, 2))
            && $this->_isUniqueRow(array_slice($row, 3, 2))
        ) {
            // case for z-xx-yy
            return true;
        }

        return false;
    }

    /**
     * Три кости одинакового достоинства
     * @param $row
     * @return bool
     */
    private function _isTrips($row)
    {
        return ($this->_isUniqueRow(array_slice($row, 0, 3))
            || $this->_isUniqueRow(array_slice($row, 1, 3))
            || $this->_isUniqueRow(array_slice($row, 2, 3))
        );
    }

    /**
     * @param $row
     * @return bool
     */
    private function _isLilStraight($row)
    {
        $row = array_unique($row);
        if (count($row) < 4) {
            return false; // Недостаточно элементов
        } elseif (count($row) === 4) {
            return $this->_isIncreasing($row);
        } else {
            return ($this->_isIncreasing(array_slice($row, 1))
                || $this->_isIncreasing(array_slice($row, 0, -1))
            );
        }
    }

    /**
     * @param $row
     * @return bool
     */
    private function _isBigStraight($row)
    {
        return $this->_isIncreasing($row);
    }

    /**
     * Проверка на возрастание массива
     * @param $row
     * @return bool
     */
    private function _isIncreasing($row)
    {
        for ($i = 0; $i < count($row) - 1; $i++) {
            if ($row[$i + 1] - $row[$i] !== 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * "Пара" плюс "сэт"
     * @param $row
     * @return bool
     */
    private function _isFullHouse($row)
    {
        if (
            $this->_isUniqueRow(array_slice($row, 0, 2))
            && $this->_isUniqueRow(array_slice($row, 2))
        ) {
            // case for xx-yyy
            return true;
        }

        if (
            $this->_isUniqueRow(array_slice($row, 0, 3))
            && $this->_isUniqueRow(array_slice($row, 3))
        ) {
            // case for xxx-yy
            return true;
        }
        return false;
    }

    /**
     * Четыре кости одинакового достоинства
     * @param $row
     * @return bool
     */
    private function _isQuad($row)
    {
        return ($this->_isUniqueRow(array_slice($row, 1))
            || $this->_isUniqueRow(array_slice($row, 0, -1))
        );
    }

    /**
     * Пять костей одинакового достоинства.
     * A.k.a. все кости одного номинала
     * @param $row
     * @return bool
     */
    private function _isPoker($row)
    {
        return $this->_isUniqueRow($row);
    }

    /**
     * @param $row
     * @return bool Возвращает TRUE если все элементы массива одинаковые
     */
    private function _isUniqueRow($row)
    {
        if (count(array_unique($row)) === 1) {
            return true;
        } else {
            return false;
        }
    }
}

class HandType
{
    const
        POKER = 1,
        QUAD = 2,
        FULL_HOUSE = 3,
        BIG_STRAIGHT = 4,
        LIL_STRAIGHT = 5,
        TRIPS = 6,
        TWO_PAIR = 7,
        PAIR = 8,
        CHANCE = 9;

    /**
     * @param $hand_type_id
     * @return string
     */
    public static function getName($hand_type_id)
    {
        switch ($hand_type_id) {
            case self::POKER:
                return 'Покер';
            case self::QUAD:
                return 'Каре';
            case self::FULL_HOUSE:
                return 'Фул Хаус';
            case self::BIG_STRAIGHT:
                return 'Большой стрит';
            case self::LIL_STRAIGHT:
                return 'Малый стрит';
            case self::TRIPS:
                return 'Сэт';
            case self::TWO_PAIR:
                return 'Две пары';
            case self::PAIR:
                return 'Пара';
            case self::CHANCE:
                return 'Шанс';
            default:
                return '-';
        }
    }
}
