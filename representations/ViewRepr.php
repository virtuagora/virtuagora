<?php

class ViewRepr implements ReprInterface {

    public function shwCollection($ctrl, $paginator) {
        $ctrl->controlQuery($paginator);
    }

    public function shwResource($ctrl, $model) {
        $ctrl->controlGet($model);
    }

}
