<?php
/**
 * Класс для постраничного вывода табличного результата запроса из базы с валидацией, фильтрами, сортировками.
 * TODO Разбить на несколько классов (каждому свои задачи)
 */

abstract class cTable{

    //Дефолтовый номер страницы
    const DEFAULT_PAGE_NUMBER = 1;

    //Массив преобразования имен порядка сортировки
    private $sort_order_names = array('back' => 'DESC','forward' => 'ASC');

    //Дефолтовое поле сортировки
    private $default_sort_field;

    //Дефолтовый порядок сортировки
    private $default_sort_order;

    //По каким полям вообще доступна сортировка
    private $sorts;

    //Количество страниц
    public $count_pages;

    //Количество рядов на странице
    protected $count_items_on_page;

    //Номер страницы
    protected $page_number;

    //Ряды итоговой таблицы
    public $rows;

    //Поле, по которому сортируем
    protected $sort_field;

    //Порядок сортировки
    protected $sort_order;

    //Чекбоксы в форме. Для них отдельная тема, т.к. дефолтовое значение может быть true, а снятый чекбокс не попадет в $_GET.
    protected $checkboxes;

    //Фильтры по умолчанию
    protected $default_filters;

    //Обработанные фильтры из $_GET
    public $real_filters;

    //Объект для работы с БД.
    protected $db;

    //Текст в WHERE в sql запросе
    protected $sql_filter;

    //С какого ряда делать выборку (в зависимости от номера страницы и кол-ва на стр. Например если 2ая стр и по 10 на стр, то с 11 ряда)
    protected $row_from;

    //По какой ряд делать выборку
    protected $row_to;

    // Максимальное количество страниц на панели навигации (не считая "...")
    private $count_pages_on_navigation_panel;

    //Ссылка для навигации без указания номера страницы. Например /delivery/visit_stat_v2/?page=
    public $navigation_href;

    /**
     * @var array Параметры навигации. Массив, состоящий из след. элементов:
     *         int navigation_from - Крайний левый номер страницы на панели навигации
     *         int navigation_to    - Крайний правый номер страницы на панели навигации
     *         bool left_suspension_points - Определяет существование левого многоточия на панели навигации
     *         bool right_suspension_points - Определяет существование правого многоточия на панели навигации
     *         int current_page - Текущая страница
     */
    public $navigation_params;


    /**
     * Генерирует текст where для вставки в sql запрос
     * @return mixed
     */
    abstract protected function generate_sql_filter();

    /**
     * Возвращает строку запроса
     * @return mixed
     */
    abstract protected function generate_query_string_with_pagination();

    /**
     * Возвращает сколько всего рядов
     *
     * @return mixed
     */
    abstract protected function get_count_all_items();

    /**
     * Устанавливает реальный фильтр. Если в параметрах запроса передали какую-то хренотень.
     * @param $name имя фильтра
     * @param $value значение фильтра в $_GET
     * @return mixed обработанное значение фильтра
     */
    abstract protected function get_real_filter($name,$value);

    public function __construct(){
        $this->db = cDataBase::getInstance();
    }

    /**
     * Общий метод для всех наследуемых классов. Вызывается в конструкторе наследуемых классов.
     */
    protected function general($params){

        foreach ($params as $key => $value){
            $this->$key = $value;
        }

        $this->sort_order =         $this->get_real_sort_order();
        $this->sort_field =     $this->get_real_sort_field();
        $this->real_filters =     $this->get_real_filters();

        $this->sql_filter =        $this->generate_sql_filter();

        $this->count_pages =     $this->get_count_pages();

        $this->page_number =    $this->get_real_page();

        if (!$this->page_number)
            show404();

        $this->row_from =        $this->count_items_on_page * ($this->page_number - 1) + 1;
        $this->row_to =            $this->count_items_on_page * $this->page_number;

        $this->rows =             $this->get_rows($this->generate_query_string_with_pagination());

        $this->navigation_params = $this->get_navigation_params();

        $this->navigation_href = $this->get_navigation_href();

    }



    /**
     * Возвращает номер страницы или false, если такой страницы нет
     */
    protected function get_real_page(){
        if (!isset($_GET['page']))
            return self::DEFAULT_PAGE_NUMBER;
        elseif (!$this->count_pages or !is_numeric($_GET['page']) or $_GET['page'] > $this->count_pages)
            return false;
        else
            return $_GET['page'];
    }

    //Создает массив с рядами из sql запроса
    private function get_rows($query){
        $rows = $this->db->select_query($query,__FILE__,__LINE__);
        if ($rows)
            return $rows;
        return false;
    }

    /**
     * @return int Возвращает количество страниц
     */
    private function get_count_pages(){
        return ceil($this->get_count_all_items() / $this->count_items_on_page);
    }

    /**
     * Устанавливает реальные фильтры (если в $_GET пришла всякая лабуда)
     */
    protected function get_real_filters(){
        foreach ($this->default_filters as $name => $value){
            //Не чекбоксы
            if (!in_array($name,$this->checkboxes)){
                if (isset($_GET[$name]))
                    $real_filters[$name] = $this->get_real_filter($name,$_GET[$name]);
                else
                    $real_filters[$name] = $this->default_filters[$name];
            }
            //Обработка чекбоксов (если форма отправлена - смотрим по наличию параметра, иначе: если отправляли через строку запроса или перешли по ссылке и параметр есть, то преобразуем в булево значение, а если нет - берем дефолтовое значение
            else{
                $real_filters[$name] = isset($_GET['form_sent']) ? isset($_GET[$name]) : ((isset($_GET[$name]) ? (bool)$_GET[$name] : (bool)$this->default_filters[$name] ));
            }
        }

        return isset($real_filters) ? $real_filters : null;
    }

    /**
     * @return mixed Обработанное поле сортировки
     */
    private function get_real_sort_field(){
        if (isset($_GET['sort_field']) && in_array($_GET['sort_field'],$this->sorts))
            return $_GET['sort_field'];

        return $this->default_sort_field;
    }

    /**
     * @return mixed Обработанный порядок сортировки
     */
    private function get_real_sort_order(){
        if (isset($_GET['sort_order']) && array_key_exists($_GET['sort_order'],$this->sort_order_names))
            return $this->sort_order_names[$_GET['sort_field']];

        return $this->default_sort_order;
    }

    private function get_navigation_params(){
        if ($this->count_pages < 2 or $this->count_items_on_page < 1 or $this->count_pages_on_navigation_panel < 1)
            return false;

        $navigation_params['current_page'] = $this->page_number;

        //Количество страниц слева от текущей страницы на панели навигации:
        $count_left_pages = ceil(($this->count_pages_on_navigation_panel - 1) / 2);

        $left_number = $this->page_number - $count_left_pages;
        $left_number = $left_number > 0 ? $left_number : 1;

        $right_number = $left_number + $this->count_pages_on_navigation_panel - 1;

        if ($right_number > $this->count_pages){
            $right_number = $this->count_pages;
            $left_number = $right_number - $this->count_pages_on_navigation_panel + 1;
            $left_number = $left_number > 0 ? $left_number : 1;
        }

        $navigation_params['navigation_from'] = $left_number;
        $navigation_params['navigation_to'] = $right_number;
        $navigation_params['left_suspension_points'] = $navigation_params['navigation_from'] > 1;
        $navigation_params['right_suspension_points'] = $navigation_params['navigation_to'] < $this->count_pages;


        return $navigation_params;
    }

    private function get_navigation_href(){

        $uri = &$_SERVER['REQUEST_URI'];

        // Если в строке запроса нет параметров
        if (!strpos($uri,'?'))
            return $uri.'?page=page_number_pattern';
        // Если есть другие параметры, но нет page
        elseif (!empty($_GET) && !isset($_GET['page']))
            return $uri.'&page=page_number_pattern';
        else
            return preg_replace('/page=[0-9]+/','page=page_number_pattern',$uri);
    }

}