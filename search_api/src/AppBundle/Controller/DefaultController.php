<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Query;
use Elastica\Query\Match;
use Elastica\Query\FunctionScore;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {
        $search = $this->container->get('fos_elastica.index.categories_en.category');

        $query = new Query();

        // Put whatever you want here
        $searchQuery = new Match();
        $searchQuery->setField('category_description', 'category');
/*
        $functionScore = new FunctionScore();
        $functionScore->addDecayFunction(
            'gauss',
            'ranking',
            '100',
            '50',
            '80',
            0.7
        );
        $functionScore->setQuery($searchQuery);
        $query->setQuery($functionScore);
*/

        $query->setQuery($searchQuery);

echo json_encode($query->getQuery()->toArray());
        $resultset = $search->search($query);
        var_dump($resultset->count());

        return new Response('', 200);
    }
}
