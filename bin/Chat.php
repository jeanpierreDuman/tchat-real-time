<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $messages;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->messages = [];
    }

    // Quand l'utilisateur se connecte
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        if(count($this->messages) > 0) {
            foreach($this->messages as $message) {
                $conn->send($message);
            }
        }
    }

    // Quand l'utilisateur reçoit un message
    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            $client->send($msg);
        }

        array_push($this->messages, $msg);
    }

    // Quand l'utilisateur se déconnecte
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
