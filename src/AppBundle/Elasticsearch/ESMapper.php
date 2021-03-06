<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Entity\Vacancy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Person;
use AppBundle\Entity\Skill;

/**
 * Class containing all logic to map an es search result in json format to the corresponding entities.
 */
class ESMapper{
    /**
     * Determines whether a value is an entity by checking for the presence of the string "Entity".
     * @param  String   $value      part of a json string constituting a value
     * @return boolean              true or false
     */
    private function isEntity($value)
    {
        return substr($value, 2, 6) == "Entity";
    }

    /**
     * Dynamically converts a json string to an entity in the same bundle.  It cannot handle nested entities @ this time.
     * @param  json     $json   a json-string
     * @return dynamic          the entity mapped from the json
     */
    private function jsonToEntity($json)
    {
        $json = json_decode($json, true);
        $classname = "AppBundle\Entity\\".$json["Entity"];
        $entity = new $classname();
        $entity->setId($json["Id"]);
        $values = $json["Values"]; //TODO check why this is extracted into a seperate var
        foreach ($values as $key => $value) {
            if (!is_null($value))
            {
                $entity->{"set".$key}($value);
            }
        }
        return $entity;
    }

    /**
     * Method to convert multiple ES hits into entities from this bundle.  It can handle nested entities as it checks each value to see whether it is an entity itself (using the isEntity() method from this class).
     * Note: it cannot handle values that are an array itself.  Meaning nesting arrays is out of the question.
     * @param json      $hits   A json string representing multiple entities.
     * @return array            An array containing the mapped entities.
     */
    public function getEntities($hits)
    {
        $entities = array();
        for ($i=0; $i < count($hits); $i++)
        {
            $self = $hits[$i];
            $source = $self["_source"];
            $entity = $this->getEntity($self["_type"], $source);
            if(!empty($entity)){
                $id = $self["_id"];
                $entity->setId($id);
                array_push($entities, $entity);
            }
            else{
                var_dump($self);
                echo "<br/><br/>";
            }
        }

        return $entities;
    }

    public function getEntity($name, $source){
        if(!($name == 'nomap')){
            $classname = "AppBundle\Entity\\" . ucfirst($name);
            $entity = new $classname();
            if(array_key_exists('location', $source)){
                unset($source['location']);
            }
            if(array_key_exists('likers', $source)){
                unset($source['likers']);
            }

            foreach ($source as $key => $value) {//iterate over all properties
                if(!empty($value)){
                    if(is_array($value)){ // if true it can be an object or an array
                        if(array_key_exists('entity', $value)){ // it's an object and we can map it recursively
                            $name = $value['entity'];
                            unset($value['entity']);
                            $value = $this->getEntity($name, $value);
                            if(!empty($value)){
                                $entity->{ "set" . ucfirst($key) }($value);
                            }
                        } else { //it's an array: every item in it will be an object which we can again map recursively
                            foreach ($value as $key2 => $object) {
                                $name = $object['entity'];
                                unset($object['entity']);
                                $method = '';

                                switch ($name) { //made into a switch so it can be expanded easily if that need arises in the future
                                    case 'skill':
                                        $method = 'addSkill';
                                        break;
                                    case 'sector':
                                        $name = 'skill';
                                        $method = 'addSector';
                                        break;
                                    case 'organisation':
                                        $method = 'addOrganisation';
                                        break;
                                }

                                $result = $this->getEntity($name, $object);
                                if(!empty($result)){
                                    $entity->{ $method }($result);
                                }
                            }
                        }
                    } else { //it's a simple property
                        $entity->{ "set" . ucfirst($key) }($value);
                    }
                }
            }
            return $entity;
        }
    }
}
