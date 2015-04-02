<?php

class ViewRepr implements ReprInterface {

    public function shwCollection($ctrl, $paginator) {
        $ctrl->executeListCtrl($paginator);
    }

    public function shwResource($ctrl, $model) {
        $ctrl->executeGetCtrl($model);
    }

    public function getName() {
        return 'view';
    }

}
