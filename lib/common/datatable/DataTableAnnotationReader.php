<?php


namespace CsrDelft\common\datatable;


use CsrDelft\common\CsrException;
use CsrDelft\common\datatable\annotation\DataTable;
use CsrDelft\common\datatable\annotation\DataTableColumn;
use CsrDelft\common\datatable\annotation\DataTableKnop;
use CsrDelft\common\datatable\annotation\DataTableRowKnop;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DataTableAnnotationReader {
	/**
	 * @var Reader
	 */
	private $reader;
	/**
	 * @var NameConverterInterface
	 */
	private $converter;
	/**
	 * @var \ReflectionClass
	 */
	private $reflectionClass;
	/**
	 * @var annotation\DataTable
	 */
	private $dataTable;
	/**
	 * @var \CsrDelft\view\datatable\knoppen\DataTableKnop[]
	 */
	private $knoppen;
	/**
	 * @var \CsrDelft\view\datatable\knoppen\DataTableRowKnop[]
	 */
	private $rowKnoppen;
	/**
	 * @var \CsrDelft\view\datatable\DataTableColumn[]
	 */
	private $properties;
	/**
	 * @var \CsrDelft\view\datatable\DataTableColumn[]
	 */
	private $methods;

	public function __construct($class) {
		$this->reader = new AnnotationReader();
		$this->converter = new CamelCaseToSnakeCaseNameConverter();
		$this->reflectionClass = new \ReflectionClass($class);

		$this->initClass();
		$this->initProperties();
		$this->initMethods();
	}

	private function initProperties() {
		$reflectionProperties = $this->reflectionClass->getProperties();

		$this->properties = [];

		foreach ($reflectionProperties as $property) {
			/** @var \CsrDelft\common\datatable\annotation\DataTableColumn|null $propertyAnnotation */
			if ($propertyAnnotation = $this->reader->getPropertyAnnotation($property, \CsrDelft\common\datatable\annotation\DataTableColumn::class)) {
				if (!$propertyAnnotation->name) {
					$propertyAnnotation->name = $property->name;
				}
				$this->properties[$property->name] = $propertyAnnotation;
			} elseif ($this->reader->getPropertyAnnotation($property, Id::class)) {
				$column = new DataTableColumn();
				$column->id = true;
				$column->name = $property->name;
				$column->hidden = true;
				$this->properties[$property->name] = $column;
			}
		}
	}

	private function initMethods() {
		$reflectionMethods = $this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

		$this->methods = [];
		foreach ($reflectionMethods as $method) {
			if ($method->getDeclaringClass()->name !== $this->reflectionClass->name) {
				continue;
			}

			/** @var \CsrDelft\common\datatable\annotation\DataTableColumn|null $methodAnnotation */
			if ($methodAnnotation = $this->reader->getMethodAnnotation($method, \CsrDelft\common\datatable\annotation\DataTableColumn::class)) {
				if (!$methodAnnotation->name) {
					$accessor = preg_match('/^(get)(.+)$/i', $method->name, $matches);
					if ($accessor) {
						$attributeName = lcfirst($matches[2]);

						$methodAnnotation->name = $this->converter->normalize($attributeName);
					} else {
						throw new CsrException("Methode " . $method->name . " in " . $this->reflectionClass->name . " begint niet met 'get' en heeft wel het DataTableColumn attribuut.");
					}
				}

				$this->methods[$method->name] = $methodAnnotation;
			}
		}
	}

	/**
	 * @return \CsrDelft\view\datatable\DataTableColumn[]
	 */
	public function getProperties() {
		return $this->properties + $this->methods;
	}

	public function initClass() {
		$classAnnotation = $this->reader->getClassAnnotations($this->reflectionClass);
		$this->knoppen = [];
		$this->rowKnoppen = [];

		foreach ($classAnnotation as $annotation) {
			if ($annotation instanceof DataTableKnop) {
				$this->knoppen[] = $annotation->getKnop();
			}

			if ($annotation instanceof DataTable) {
				$this->dataTable = $annotation;
			}

			if ($annotation instanceof DataTableRowKnop) {
				$this->rowKnoppen = $annotation->getKnop();
			}
		}
	}

	public function getKnoppen() {
		return $this->knoppen;
	}

	public function getRowKnoppen() {
		return $this->rowKnoppen;
	}

	public function getConfig() {
		return $this->dataTable;
	}
}
