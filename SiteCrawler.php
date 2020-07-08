<?php


class SiteCrawler
{
// возвращает контент страницы с указанным URL
    private function getContent($url)
    {
        return file_get_contents($url);
    }


    // возвращает массив извлеченных данных
    private function extractData($content)
    {
        return [
            'productName' => $this->getProductName($content),
            'description' => $this->getDescription($content),
            'specifications' => $this->getSpecifications($content),
        ];
    }

    private function getProductName(string $html): string
    {
        $productNamePattern = '/<h1\sclass=\'goodtitlemain\'>([а-яА-Яa-zA-Z]|\s|[0-9]|-|,|\(|\))+/u';
        preg_match($productNamePattern, $html, $matches);
        return preg_replace('/<h1\sclass=\'goodtitlemain\'>(\s|\r|\n)+/', '', $matches[0]);
    }

    private function getDescription(string $html): string
    {
        $descriptionPattern = '/<div.*>[^>]*<\/div>/u';
        preg_match_all($descriptionPattern, $html, $matches);
        //die(var_dump($matches));
        return preg_replace('/<[^>]*>/', '', $matches[0][45]);
    }

    private function getSpecifications(string $html): array
    {
        preg_match_all("/tr.class=\'\'>\s(.+)\s(.+)/u", $html, $matches);
        $specifications = array_combine($matches[1], $matches[2]);

        $keysArray = [];
        $valuesArray = [];

        foreach ($specifications as $key => $value) {
            $key = preg_replace('/<[^>]*>|\[\s\?\s\]/', '', $key);
            $value = preg_replace('/<[^>]*>/', '', $value);
            array_push($keysArray, $key);
            array_push($valuesArray, $value);
        }

        $specifications = array_combine($keysArray, $valuesArray);

        $pricePattern = '/itemprop="price">(.+)<\/span>/u';
        preg_match_all($pricePattern, $html, $matches);
        $price = html_entity_decode($matches[1][0]);

        $specifications['Цена'] = $price; //ключ на кириллице, чтобы соответствовал другим

        return $specifications;
    }

    public function handle($url)
    {
        return $this->extractData($this->getContent($url));
    }
}

