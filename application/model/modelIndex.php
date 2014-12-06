<?php

class modelIndex extends model {

    public function getName() {
        $mas = $this->DBselect('articles', 0);
        return $mas;
    }

}