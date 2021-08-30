<?php

namespace AskNicely\Model;

interface ModelInterface {
    /**
     * Run validation checks before saving.
     * @return mixed
     */
    public function validateBeforeSave();
}