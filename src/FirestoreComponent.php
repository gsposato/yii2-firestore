<?php

namespace gsposato\yii2firestore;

class FirestoreComponent extends \yii\base\Component
{
    public $project_key;
    public $api_key;

    /**
     * Get firestore document
     * @param String $collection_name
     * @param String $document_name
     */
    public function get($collection_name, $document_name)
    {
        $client = $this->client();
        $document = $this->document();
        return $client->getDocument($collection_name, $document_name);
    }

    /**
     * Add firestore document
     * @param String $collection_name
     * @param Array $data
     */
    public function add($collection_name, $data)
    {
        $client = $this->client();
        $document = $this->document();
 
        foreach ($data as $key => $value)
        {
            $document->setString($key, $value);
        }

        return $client->addDocument($collection_name, $document);
    }

    /**
     * Update firestore document
     * @param String $collection_name
     * @param String $document_name
     */
    public function update($collection_name, $document_name)
    {
        $client = $this->client();
        $document = $this->document();
        return $client->updateDocument($collection_name, $document_name, $document, true);
    }

    /**
     * Remove firestore document
     * @param String $collection_name
     * @param String $document_name
     */
    public function remove($collection_name, $document_name)
    {
        $client = $this->client();
        $document = $this->document();
        return $client->deleteDocument($collection_name, $document_name);
    }

    /**
     * Initialize firestore client wrapper
     */
    protected function client()
    {
        return new FirestoreApiClient(
            $this->project_key, $this->api_key
        );
    }

    /**
     * Initialize firestore document wrapper
     */
    protected function document()
    {
        return new FirestoreDocument();
    }
}
