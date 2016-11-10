<?php
namespace TaskBundle\Services;

/**
 * Class Context
 * @package TaskBundle\Services
 */
class Context implements \JsonSerializable
{
    /**
     * Attributes array
     *
     * @var array
     */
    protected $attributes;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->assign($attributes);
        }
    }

    /**
     * Assign values with rewriting existing key values
     *
     * @param array $attributes
     * @return Context
     */
    public function assign(array $attributes): Context
    {
        foreach ($attributes as $name => $value) {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

    /**
     * Check has attribute
     *
     * @param string $attribute
     * @return bool
     */
    public function has(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * Set new value to attribute
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return Context
     */
    public function set(string $attribute, $value): Context
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    /**
     * Append value to attribute with array type
     *
     * @param string $attribute
     * @param array  $value
     *
     * @return Context
     */
    public function append(string $attribute, array $value): Context
    {
        $previous = (array)$this->get($attribute, []);
        $this->set($attribute, array_merge_recursive($previous, $value));
        return $this;
    }

    /**
     * Get attribute value or return default
     *
     * @param string $attribute
     * @param mixed  $default
     * @return mixed
     */
    public function get($attribute, $default = null)
    {
        return $this->has($attribute) ? $this->attributes[$attribute] : $default;
    }

    /**
     * Return context data as array
     *
     * @return array
     */
    function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}
