<?php

namespace Src\Theme;

abstract class ResultsViewer extends View{
    public array $headers;
    public array $data;
    public bool $orderable = false;
    public function setHeaders(array $headers){
        $this->headers = $headers;
        return $this;
    }

    public function setData(array $data){
        $this->data = $data;
        return $this;
    }

    public function setOrderable(bool $orderable)
    {
        $this->orderable = $orderable;
        return $this;
    }
}