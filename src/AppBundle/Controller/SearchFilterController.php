<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Person;
use AppBundle\Entity\Skill;

class SearchFilterController extends Controller
{
    private function getRecentSearches($nr)
    {
        return $this->getDoctrine()
            ->getRepository("AppBundle:SearchFilter")
            ->createQueryBuilder("f")
            ->where("f.owner = :owner")
            ->orderBy("f.id", "DESC")
            ->setparameter("owner", $this->getuser())
            ->setMaxResults($nr)
            ->getQuery()
            ->getResult();
    }

    public function listRecentSearchFiltersAction($nr = 5)
    {
        $filters = $this->getRecentSearches($nr);

        return $this->render("searchResult/searchFilter.html.twig",
            ["searchFilters" => $filters]);
    }
}