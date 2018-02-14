<?php

namespace gsposato\yii2firestore;

class FirestoreDocument {

    private $fields = [];
    private $name = null;
    private $createTime = null;
    private $updateTime = null;

        /**
        @example
        {
         "name": "projects/{project_id}/databases/(default)/documents/{collectionName}/{documentId}",
         "fields": {
          "hello": {
           "doubleValue": 3
          }
         },
         "createTime": "2017-10-18T21:27:33.186235Z",
         "updateTime": "2017-10-18T21:27:33.186235Z"
        }
         */
    public function __construct($json=null) {
        if ($json !== null) {
            $data = json_decode($json, true, 16);
            // Meta properties
            $this->name = $data['name'];
            $this->createTime = $data['createTime'];
            $this->updateTime = $data['updateTime'];
            // Fields
            foreach ($data['fields'] as $fieldName => $value) {
                $this->fields[$fieldName] = $value;
            }
        }
    }

    public function getName() {
        return $this->name;
    }

    public function setString($fieldName, $value) {
        $this->fields[$fieldName] = [
            'stringValue' => $value
        ];
    }

    public function setDouble($fieldName, $value) {
        $this->fields[$fieldName] = [
            'doubleValue' => floatval($value)
        ];
    }

    public function setArray($fieldName, $value) {
        $this->fields[$fieldName] = [
            'arrayValue' => $value
        ];
    }

    public function setBoolean($fieldName, $value) {
        $this->fields[$fieldName] = [
            'booleanValue' => !!$value
        ];
    }

    public function setInteger($fieldName, $value) {
        $this->fields[$fieldName] = [
            'integerValue' => intval($value)
        ];
    }

    public function get($fieldName) {
        if (array_key_exists($fieldName, $this->fields)) {
            return reset($this->fields);
        }
        throw new Exception('No such field');
    }

    public function toJson() {
        return json_encode([
            'fields' => $this->fields
        ]);
    }

}
