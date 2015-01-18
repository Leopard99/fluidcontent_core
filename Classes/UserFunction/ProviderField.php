<?php
namespace FluidTYPO3\FluidcontentCore\UserFunction;

/*
 * This file is part of the FluidTYPO3/FluidcontentCore project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\FluidcontentCore\Provider\CoreContentProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Variants Field TCA user function
 */
class ProviderField {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var CoreContentProvider
	 */
	protected $provider;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->provider = $this->objectManager->get('FluidTYPO3\FluidcontentCore\Provider\CoreContentProvider');
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function createVariantsField(array $parameters) {
		$extensionKeys = $this->provider->getVariantExtensionKeysForContentType($parameters['row']['CType']);
		$defaults = $this->provider->getDefaults();
		$preSelected = $parameters['row']['content_variant'];
		if (CoreContentProvider::MODE_PRESELECT === $defaults['mode'] && TRUE === empty($preSelected)) {
			$preSelected = $defaults['variant'];
		}
		if (TRUE === is_array($extensionKeys) && 0 < count($extensionKeys)) {
			$options = $this->renderOptions($extensionKeys);
		} else {
			$options = array();
		}
		return $this->renderSelectField($parameters, $options, $preSelected);
	}

	/**
	 * @param array $variants
	 * @return array
	 */
	protected function renderOptions(array $variants) {
		$options = array();
		foreach ($variants as $variantSetup) {
			list ($extensionKey, $labelReference, $icon) = $variantSetup;
			$translatedLabel = LocalizationUtility::translate($labelReference, $extensionKey);
			if (NULL === $translatedLabel) {
				$translatedLabel = $extensionKey;
			}
			if (NULL !== $icon) {
				$translatedLabel = '<img src="' . $icon . '" alt="' . $extensionKey . '" /> ' . $translatedLabel;
			}
			$options[$extensionKey] = $translatedLabel;
		}
		return $options;
	}

	/**
	 * @param array $parameters
	 * @param array $options
	 * @param mixed $selectedValue
	 * @return string
	 */
	protected function renderSelectField($parameters, $options, $selectedValue) {
		$hasSelectedValue = (TRUE === empty($selectedValue) || TRUE === in_array($selectedValue, $options));
		$selected = (TRUE === empty($selectedValue) ? ' selected="selected"' : NULL);
		$html = array(
			'<select class="select" name="' . $parameters['itemFormElName'] . '" onchange="' . $parameters['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] . ';' . $parameters['fieldChangeFunc']['alert'] . '">',
			'<option' . $selected . ' value="">' . LocalizationUtility::translate('tt_content.nativeLabel', 'FluidcontentCore') . '</option>'
		);
		foreach ($options as $value => $label) {
			$selected = $value === $selectedValue ? ' selected="selected"' : NULL;
			$html[] = '<option' . $selected . ' value="' . $value . '">' . $label . '</option>';
		}
		if (FALSE === $hasSelectedValue) {
			$html[] = '<option selected="selected">INVALID: ' . $selectedValue . '</option>';
		}
		$html[] = '</select>';
		return implode(LF, $html);
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function createVersionsField(array $parameters) {
		$defaults = $this->provider->getDefaults();
		$preSelectedVariant = $parameters['row']['content_variant'];
		$preSelectedVersion = $parameters['row']['content_version'];
		if (CoreContentProvider::MODE_PRESELECT === $defaults['mode']) {
			if (TRUE === empty($preSelectedVariant)) {
				$preSelectedVariant = $defaults['variant'];
			}
			if (TRUE === empty($preSelectedVersion)) {
				$preSelectedVersion = $defaults['version'];
			}
		}

		$versions = $this->provider->getVariantVersions($parameters['row']['CType'], $preSelectedVariant);
		if (TRUE === is_array($versions) && 0 < count($versions)) {
			$options = array_combine($versions, $versions);
		} else {
			$options = array();
		}
		return $this->renderSelectField($parameters, $options, $preSelectedVersion);
	}

	/**
	 * @return string
	 */
	protected function getNoneFoundLabel() {
		return LocalizationUtility::translate('tt_content.noneFoundLabel', 'FluidcontentCore');
	}

}
