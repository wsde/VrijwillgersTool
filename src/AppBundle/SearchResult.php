<?php

namespace AppBundle;

class SearchResult
{
    private $title;
    private $body;
    private $link;

    public static function fromEntities($entities)
    {
        foreach ($entities as $entity) {
            yield SearchResult::fromEntity($entity);
        }
    }

    public static function fromEntity($entity)
    {
        $class_path = get_class($entity);
        $class_name = explode( '\\', $class_path)[2];
        return SearchResult::{"create".$class_name."Result"}($entity);
    }

    private static function createVolunteerResult($vol)
    {
        $title = $vol->getFullName();
        if ($vol->getUsername())
        {
            $title .=" ( ".$vol->getUsername()." )";
        }
        $body = $vol->getEmail()." some volunteer body examples"; // skills
        $link = "/persoon/".$vol->getId();
        return new SearchResult($title, $body, $link);
    }

    private static function createOrganisationResult($org)
    {
        $title = $org->getName();
        $body = $org->getDescription()." some organisation body example";
        $link = "/organisatie/".$org->getId();
        return new SearchResult($title, $body, $link);
    }

    private static function createVacancyResult($vac)
    {
        $title = $vac->getTitle();
        $body = $vac->getDescription()." some vacancy body example";
        $link = "/vacature/".$vac->getId();
        return new SearchResult($title, $body, $link);
    }

    private function __construct($title, $body, $link)
    {
        $this->title = $title;
        $this->body = $body;
        $this->link = $link;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getLink()
    {
        return $this->link;
    }
}
