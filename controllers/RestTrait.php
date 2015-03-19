<?php

trait RestTrait {

    public function restList($query) {
        $req = $this->request;

        $queryMaker = new QueryMaker($query, $req->get());
        $queryMaker->addFilters($this->filtrables);
        $queryMaker->addSorters($this->ordenables);
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($queryMaker->query, $url, $queryMaker->params);

        $res = $this->response;
        $res->headers->set('Content-Type', 'application/json');
        if (!empty($paginator->links)) {
            $linkArray = array();
            foreach ($paginator->links as $rel => $link) {
                $linkArray[] = '<' . $link . '>; rel="' . $rel . '"';
            }
            $res->headers->set('Link', implode(', ', $linkArray));
        }

        echo $paginator->rows->toJson();
    }

}
