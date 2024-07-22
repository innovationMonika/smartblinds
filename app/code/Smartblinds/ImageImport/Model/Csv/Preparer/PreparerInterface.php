<?php

namespace Smartblinds\ImageImport\Model\Csv\Preparer;

interface PreparerInterface
{
    public function prepare();

    public function getCsvUrl(): string;

    public function getFailedImages(): array;
}
