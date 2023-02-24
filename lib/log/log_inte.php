<?php
/**
 * @brief ILog
 * @class ILog interface
 */
interface ILog
{
    /**
     * @brief 
     * @param string $logs 
     */
    public function write($logs = "");

    /**
     * @brief 
     * @return string 
     */
    public function read();
}