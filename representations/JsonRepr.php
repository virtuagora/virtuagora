<?php

class JsonRepr implements ReprInterface {

    public function shwCollection($ctrl, $paginator) {
        $res = $ctrl->response;
        $res->headers->set('Content-Type', 'application/json');
        if (!empty($paginator->links)) {
            $res->headers->set('Link', $paginator->getLinkHeader());
        }
        echo $paginator->rows->toJson();
    }

    public function shwResource($ctrl, $model) {
        $res = $ctrl->response;
        $res->headers->set('Content-Type', 'application/json');
        echo $model->toJson();
    }

}
