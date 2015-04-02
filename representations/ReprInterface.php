<?php

interface ReprInterface {
    public function shwCollection($ctrl, $paginator);
    public function shwResource($ctrl, $model);
    public function getName();
}
