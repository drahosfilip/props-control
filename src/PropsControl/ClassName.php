<?php declare (strict_types = 1);

namespace Wavevision\PropsControl;

use Wavevision\Utils\Strings;

class ClassName
{

	private const ELEMENT_DELIMITER = '__';

	private const MODIFIER_DELIMITER = '--';

	private const SUB_BLOCK_DELIMITER = '-';

	/**
	 * @var string
	 */
	private $baseClass;

	/**
	 * @var string
	 */
	private $elementDelimiter = self::ELEMENT_DELIMITER;

	/**
	 * @var string
	 */
	private $modifierDelimiter = self::MODIFIER_DELIMITER;

	/**
	 * @var callable(): array<string>
	 */
	private $modifiersCallback;

	/**
	 * @var string
	 */
	private $subBlockDelimiter = self::SUB_BLOCK_DELIMITER;

	public function __construct(string $baseClass, callable $modifiersCallback)
	{
		$this->baseClass = $baseClass;
		$this->modifiersCallback = $modifiersCallback;
	}

	public function block(?string ...$modifiers): string
	{
		return $this->composeClassNames($this->baseClass, array_merge(($this->modifiersCallback)(), $modifiers));
	}

	public function create(string $baseClass, bool $sub = true): self
	{
		if ($sub) {
			$baseClass = $this->baseClass . $this->subBlockDelimiter . $baseClass;
		}
		return new static($baseClass, $this->modifiersCallback);
	}

	public function element(string $className, ?string ...$modifiers): string
	{
		return $this->composeClassNames($this->baseClass . $this->elementDelimiter . $className, $modifiers);
	}

	public function getBaseClass(): string
	{
		return $this->baseClass;
	}

	public function setElementDelimiter(string $elementDelimiter): self
	{
		$this->elementDelimiter = $elementDelimiter;
		return $this;
	}

	public function setModifierDelimiter(string $modifierDelimiter): self
	{
		$this->modifierDelimiter = $modifierDelimiter;
		return $this;
	}

	public function setSubBlockDelimiter(string $subBlockDelimiter): self
	{
		$this->subBlockDelimiter = $subBlockDelimiter;
		return $this;
	}

	/**
	 * @param string $className
	 * @param array<string|null> $modifiers
	 * @return string
	 */
	private function composeClassNames(string $className, array $modifiers): string
	{
		$classNames = [$className];
		foreach ($this->filterModifiers($modifiers) as $modifier) {
			$classNames[] = $className . $this->modifierDelimiter . Strings::camelCaseToDashCase($modifier);
		}
		return implode(' ', $classNames);
	}

	/**
	 * @param array<string|null> $modifiers
	 * @return array<string>
	 */
	private function filterModifiers(array $modifiers): array
	{
		return array_filter(
			$modifiers,
			function (?string $modifier): bool {
				return $modifier !== null;
			}
		);
	}
}