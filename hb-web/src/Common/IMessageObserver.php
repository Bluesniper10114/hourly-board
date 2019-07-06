<?php
namespace Common;

/**
 * Interface for controllers observing info/error/warning messages sent from models
 **/
interface IMessageObserver
{

    /**
     * Sends an error message to listeners
     * @param string $message Message
     * @return void
     **/
    public function onError($message);

    /**
     * Sends a success message to listeners
     * @param string $message Message
     * @return void
     **/
    public function onSuccess($message);

    /**
     * Sends an warning message to listeners
     * @param string $message Message
     * @return void
     **/
    public function onWarning($message);

    /**
     * Sends an info message to listeners
     * @param string $message Message
     * @return void
     **/
    public function onInfo($message);

    /**
     * Notifies listeners error and info messages should be cleared
     **/
    public function clear();

    /**
     * Checks if there are new messages in the queue
     */
    public function hasMessage();
}