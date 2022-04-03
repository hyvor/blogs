<?php
namespace App\Models\Concerns;

trait Metable
{

    public function getMeta(string $name)
    {

        if (!array_key_exists($name, $this->metableDefinition)) {
            throw new \Exception();
        }

        $meta = $this->getMetaFromTable();

        $definition = $this->metableDefinition[$name];
        $default = $definition[1];

        return array_key_exists($name, $meta) ? $meta[$name] : $default;
    
    }

    public function getAllMeta()
    {

        $ret = [];
        $meta = $this->getMetaFromTable();

        foreach ($this->metableDefinition as $name => [$type, $default]) {
            $ret[$name] = array_key_exists($name, $meta) ? $meta[$name] : $default;
        }

        return $ret;

    }

    public function setMeta(string $name, $value)
    {

        if (!array_key_exists($name, $this->metableDefinition)) {
            throw new \Exception();
        }

        $definition = $this->metableDefinition[$name];
        $valueType = $definition[0];

        if (!$this->metaValidateType($valueType, $value)) {
            throw new \Exception('Invalid Meta Type');
        }

        $this->update([
            "meta->$name" => $value
        ]);

    }

    private function getMetaFromTable() : array
    {
        $meta = $this->meta;

        if (is_string($meta)) {
            return json_decode($meta, true) ?? [];
        } else if (is_array($meta)) {
            return $meta;
        }

        return [];
    }

    private function metaValidateType(string $type, $value) : bool
    {

        $types = explode('|', $type);

        $valid = false;

        foreach ($types as $type) {

            if ($type === 'string' && is_string($value)) {
                $valid = true;
                break;
            }

            if ($type === 'int' && is_int($value)) {
                $valid = true;
                break;
            }

            if ($type === 'float' && is_float($value)) {
                $valid = true;
                break;
            }

            if ($type === 'bool' && is_bool($value)) {
                $valid = true;
                break;
            }

            if ($type === 'null' && is_null($value)) {
                $valid = true;
                break;
            }

            if (preg_match('/^enum:(.+)$/', $type, $matches)) {
                $validValues = explode(',', $matches[1]);
                if (in_array($value, $validValues)) {
                    $valid = true;
                    break;
                }
            }

        }

        return $valid;

    }

}