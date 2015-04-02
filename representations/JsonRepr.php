<?php

class JsonRepr implements ReprInterface {

    public function shwCollection($ctrl, $paginator) {
        if (!empty($paginator->links)) {
            $ctrl->response->headers->set('Link', $paginator->getLinkHeader());
        }
        echo $paginator->rows->toJson();
    }

    public function shwResource($ctrl, $model) {
        echo $model->toJson();
    }

    public function getName() {
        return 'json';
    }

}
