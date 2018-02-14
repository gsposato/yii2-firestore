<?php

namespace gsposato\yii2firestore;

class FirestoreApiClient {

    private $apiRoot = 'https://firestore.googleapis.com/v1beta1/';
    private $project;
    private $apiKey;

    function __construct($project, $apiKey) {
        $this->project = $project;
        $this->apiKey = $apiKey;
    }

    private function constructUrl($method, $params=null) {
        $params = is_array($params) ? $params : [];
        return (
            $this->apiRoot . 'projects/' . $this->project . '/' .
            'databases/(default)/' . $method . '?key=' . $this->apiKey . '&' . http_build_query($params)
        );
    }

    private function get($method, $params=null) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function post($method, $params, $postBody) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen($postBody)),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function put($method, $params, $postBody) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen($postBody)),
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function patch($method, $params, $postBody) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen($postBody)),
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function delete($method, $params) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getDocument($collectionName, $documentId) {
        if ($response = $this->get("documents/$collectionName/$documentId")) {
            return new FirestoreDocument($response);
        }
    }

        /**
            This does not work
         */
    public function setDocument($collectionName, $documentId, $document) {
        return $this->put(
            "documents/$collectionName/$documentId", 
            [ ],
            $document->toJson()
        );
    }

    public function updateDocument($collectionName, $documentId, $document, $documentExists=null) {
        $params = [];
        if ($documentExists !== null) {
            $params['currentDocument.exists'] = !!$documentExists;
        }
        return $this->patch(
            "documents/$collectionName/$documentId", 
            $params,
            $document->toJson()
        );
    }

    public function deleteDocument($collectionName, $documentId) {
        return $this->delete(
            "documents/$collectionName/$documentId", []
        );
    }

    public function addDocument($collectionName, $document) {
        return $this->post(
            "documents/$collectionName", 
            [],
            $document->toJson()
        );
    }

}
