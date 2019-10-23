<?php declare (strict_types = 1);

namespace Wavevision\PropsControl;

use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Wavevision\Utils\Strings;

/**
 * @property-read PropsControlTemplate $template
 */
abstract class PropsControl extends BaseControl
{

	public const CLASS_NAME = '';

	public const CLASS_NAME_MODIFIERS = [];

	private const MODIFIERS = 'modifiers';

	private const PROPS = 'props';

	private const TEMPLATE_CLASS_NAME = 'className';

	public function getBaseClassName(): string
	{
		return static::CLASS_NAME ?: Strings::camelCaseToDashCase($this->getNameFromClass());
	}

	public function getNameFromClass(): string
	{
		return Strings::getClassName(static::class, true);
	}

	/**
	 * @param object|mixed[] $props
	 */
	public function render($props): void
	{
		$this->prepareRender($props);
		$this->template->render();
	}

	/**
	 * @param object|mixed[] $props
	 * @return string
	 */
	public function renderToString($props): string
	{
		$this->prepareRender($props);
		return $this->template->renderToString();
	}

	protected function beforeMapPropsToTemplate(object $props): void
	{
	}

	protected function beforeRender(object $props): void
	{
	}

	/**
	 * @return string[]
	 */
	final protected function getMappedModifiers(): array
	{
		return $this->template->{self::MODIFIERS} ?? [];
	}

	/**
	 * @param string $prop
	 * @return mixed
	 */
	final protected function getMappedProp(string $prop)
	{
		if ($props = $this->getMappedProps()) {
			return $props->$prop ?? null;
		}
		return null;
	}

	final protected function getMappedProps(): ?object
	{
		return $this->template->{self::PROPS} ?? null;
	}

	final protected function mapPropsToTemplate(object $props): void
	{
		if ($props instanceof Props) {
			$props = $props->process();
		}
		$this->beforeMapPropsToTemplate($props);
		$this->template->{self::PROPS} = $props;
		$this->template->{self::MODIFIERS} = [];
		foreach (static::CLASS_NAME_MODIFIERS as $modifier) {
			if ($this->getMappedProp($modifier)) {
				$this->template->{self::MODIFIERS}[] = $modifier;
			}
		}
		$this->beforeRender($props);
	}

	/**
	 * @inheritDoc
	 */
	protected function getTemplateParameters(): array
	{
		return [self::TEMPLATE_CLASS_NAME => $this->createClassName()];
	}

	/**
	 * @param mixed[]|object $props
	 */
	final protected function prepareRender($props): void
	{
		if (is_array($props)) {
			$props = $this->createProps($props);
		}
		if (!is_object($props)) {
			throw new InvalidArgumentException(
				sprintf('Render props must be array|object, "%s" given to "%s".', gettype($props), static::class)
			);
		}
		$this->mapPropsToTemplate($props);
	}

	private function createClassName(): ClassName
	{
		return new ClassName(
			$this->getBaseClassName(),
			function (): array {
				return $this->getMappedModifiers();
			}
		);
	}

	/**
	 * @param mixed[] $props
	 * @return Props
	 */
	private function createProps(array $props): Props
	{
		$class = static::class . Strings::firstUpper(self::PROPS);
		if (!class_exists($class)) {
			throw new InvalidStateException("Props definition '$class' does not exist.");
		}
		return new $class($props);
	}
}
