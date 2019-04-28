<?php


namespace App\Core\Utils;


use PDO;
use PDOStatement;
use Tracy\Debugger;

class LoggedPDOStatement extends PDOStatement
{
    /**
     * @var LoggedPDO
     */
    private $PDO;

    /**
     * @var array
     */
    protected $inputParams;

    protected function __construct(PDO &$PDO)
    {
        $this->PDO = $PDO;
    }

    public function execute($inputParams = null) {
        if ($inputParams) {
            $this->inputParams = $inputParams;
        }
        $start = microtime(true);

        if ($executed = parent::execute($inputParams)) {
            $this->PDO->log($this->queryString, microtime(true) - $start, $inputParams);
            return $executed;


        }
    }



//    public function execute($input_parameters = null) {
//        $start = microtime(true);
//        $result = $this->statement->execute();
//        $time = microtime(true) - $start;
//        return $result;
//    }


}