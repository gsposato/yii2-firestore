<?php

namespace gsposato\yii2firestore;

use Yii;
use \yii\web\View;
use GuzzleHttp\Client;
use common\helpers\MastrackHelper;
use \Google\Cloud\Core\Timestamp;
use \Google\Cloud\Datastore\DatastoreClient;
use \Google\Cloud\Firestore\FirestoreClient;
use \Google\Cloud\Firestore\DocumentReference;
use \Google\Cloud\Firestore\Transaction as FirestoreTransaction;

class FirestoreComponent extends \yii\base\Component
{
    public $project;
    public $credential_file;

    /**
     * Initialize other functions
     */
    public function init()
    {
        $config = array(
            "projectId" => $this->project,
            "keyFile" => json_decode(file_get_contents($this->credential_file), true)
        );

        return new FirestoreClient($config);
    }

    /**
     * Create a GeoPoint
     * @param float $latitude
     * @param float $longitude
     * @return Object
     */
    public function geopoint($latitude,$longitude)
    {
        $config = array(
            "projectId" => $this->project,
            "keyFile" => json_decode(file_get_contents($this->credential_file), true)
        );

        $datastore = new DatastoreClient($config);
        return $datastore->geoPoint($latitude, $longitude);
    }

    /**
     * Create a Timestamp
     * @param DateTime $datetime
     * @return Object
     */
    public function timestamp($datetime)
    {
        return new Timestamp($datetime);
    }

    /**
     * Get firestore document
     * @param String $collection_name
     * @param String $document_name
     */
    public function get($collection_name, $document_name)
    {
        $firestore = $this->init();

        try 
        {
            return $firestore->document($collection_name . '/' . $document_name);
        }

        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Add firestore document
     * @param String $collection_name
     * @param Array $data
     */
    public function add($collection_name, $data)
    {
        $firestore = $this->init();
        $collection = $firestore->collection($collection_name);
        $new = $collection->add($data);
        return $new->id();
    }

    /**
     * Update firestore document
     * @param String $collection_name
     * @param String $document_name
     * @param Array $data
     */
    public function update($collection_name, $document_name, $data)
    {
        $firestore = $this->init();
        $collection = $firestore->collection($collection_name)->document($document_name);
        $fieldPaths = array();

        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                array_push($fieldPaths, ["path" => $firestore->fieldPath([$key]),"value" => $value]);
                continue;
            }

            array_push($fieldPaths, ["path" => $key, "value" => $value]);
        }

        $result = $collection->update($fieldPaths);
        return $result;
    }

    /**
     * Remove firestore document
     * @param String $collection_name
     * @param String $document_name
     */
    public function remove($collection_name, $document_name)
    {
        $firestore = $this->init();
        $document = $firestore->collection($collection_name)->document($document_name);
        $document->delete();
    }

    /**
     * Remove all firestore documents by property
     * @param String $collection_name
     * @param String $property_name
     * @param String $property_value
     * @param Boolean $debug
     */
    public function removeByProperty($collection_name, $organization_name, $sub_collection_name, $property_name, $property_value)
    {
        $firestore = $this->init();
        $collection = $firestore->collection($collection_name)->document($organization_name)->collection($sub_collection_name);
        $query = $collection->where($property_name, '=', $property_value);
        $documents = $query->documents();

        foreach ($documents as $document)
        {
            if (!$document->exists())
            {
                echo "document does not exist.\n";
                continue;
            }

            echo "deleting document: " . $document->name . "\n";
            $document->delete();
            sleep(10);
        }
    }

    /**
     * Get a group of firestore documents based on specific conditions
     * @param String $collection_name
     * @param Array $conditions
     * @return Object
     */
    public function query($superCollection, $organizationKey, $subCollection, $conditions = array())
    {
        if (empty($conditions))
        {
            throw new \Exception("Query must contain at least one condition");
        }

        $firestore = $this->init();
        $collection = $firestore->collection($superCollection)->document($organizationKey)->collection($subCollection);

        switch (count($conditions))
        {

        case 1:
            $query = $collection
                ->where(
                    $conditions[0]["property"],
                    $conditions[0]["operator"], 
                    $conditions[0]["comparison"]
                );
            break;

        case 2:
            $query = $collection
                ->where(
                    $conditions[0]["property"],
                    $conditions[0]["operator"], 
                    $conditions[0]["comparison"]
                )
                ->where(
                    $conditions[1]["property"],
                    $conditions[1]["operator"], 
                    $conditions[1]["comparison"]
                );
            break;

        case 3:
            $query = $collection
                ->where(
                    $conditions[0]["property"],
                    $conditions[0]["operator"], 
                    $conditions[0]["comparison"]
                )
                ->where(
                    $conditions[1]["property"],
                    $conditions[1]["operator"], 
                    $conditions[1]["comparison"]
                )
                ->where(
                    $conditions[2]["property"],
                    $conditions[2]["operator"], 
                    $conditions[2]["comparison"]
                );
            break;

        default:
            throw new \Exception("Component can only compare 3 properties at a time.");

        }

        return $query->documents();
    }
}
