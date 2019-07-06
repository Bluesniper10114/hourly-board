<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use \Extension\Zeus\FlashMessages;

/**
 * Stores messages: info / error / warning
 */
class InfoMessageServiceProvider extends ServiceProvider implements \Common\IMessageObserver
{

    /** @var FlashMessages $messageHandler Displays cool error messages on screen */
    public $messageHandler;

    public function __construct()
    {
        $this->messageHandler = new FlashMessages();
    }

    /**
     * Sends an error message to listeners
     * @param string $message Error message
     * @return void
     */
    public function onError($message)
    {
        $this->messageHandler->error($message);
    }

    /**
     * Sends a success message to listeners
     * @param string $message Success message
     * @return void
     */
    public function onSuccess($message)
    {
        $this->messageHandler->success($message);
    }

    /**
     * Sends an warning message to listeners
     * @param string $message Warning message
     * @return void
     */
    public function onWarning($message)
    {
        $this->messageHandler->warning($message);
    }

    /**
     * Sends an info message to listeners
     * @param string $message Info message
     * @return void
     */
    public function onInfo($message)
    {
        $this->messageHandler->info($message);
    }

    /**
     * Notifies listeners error and info messages should be cleared
     * @return void
     */
    public function clear()
    {
        $this->messageHandler->clear();
        // currently the message handler library does not support clearing
    }

    /**
     * Gets last message and clears it from the queue considering that the
     * message was displayed
     * 
     * @return string|null Last message
     */
    public function display()
    {
        if ($this->messageHandler->hasMessages()) {
            return $this->messageHandler->display();
        }
        return null;
    }

    /**
     * Checks if there are any new messages in the queue
     * @return bool True if messages exist
     */
    public function hasMessage()
    {
        return $this->messageHandler->hasMessages();
    }
}