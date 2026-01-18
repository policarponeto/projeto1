<?php

    function erro($message, $status = 400)
    {
        $response = \Config\Services::response();
        return $response->setJSON([
            'error' => true,
            'message' => $message
        ])->setStatusCode($status);
    }

