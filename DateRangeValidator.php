<?php
/**
 * @copyright Copyright (c); nerburish, 2016
 * @package yii2-daterange-validator
 */

namespace nerburish\daterangevalidator;

use yii\validators\Validator;
use yii\base\DynamicModel;
use yii\validators\DateValidator;
use yii\base\InvalidConfigException;
use Yii;

class DateRangeValidator extends Validator
{	
    /**
     * @var string the type of the validator that will be used by DateValidator from Yii2 core validator.
	 * If is set as 'date' type, the from date time will be set as 00:00:00 and until date time as 23:59:59.
	 * If is set as 'datetime' type, the from and until times will be preserved as passed.	 
	 * However, if set as format 'time' or any else will throw yii\base\InvalidConfigException
	 * If not set, type 'date' will be used.
     */
    public $type = DateValidator::TYPE_DATE;	
	
    /**
     * @var string the date format expcted by DateValidator from Yii2 core validator.
     *
     * If not set, will be used the format defined in formatter component in the same way as DateValidator does
     * See DateValidator format: http://www.yiiframework.com/doc-2.0/yii-validators-datevalidator.html#
     */
    public $format;
	
	/**
     * @var string the date format for database field
     */
    public $dbFormat = 'U';

    /**
     * @var string expected to split the from date and until date.
     * If not set, will be used ' to ' by default
     */	
    public $separator = ' to ';
	
    /**
     * @var string attribute name of the model passed where the from date timestamp will be assigned
     */	
    public $fromDateAttribute = 'fromDate';

    /**
     * @var string attribute name of the model passed where the until date timestamp will be assigned
     */		
	public $untilDateAttribute = 'untilDate';	

    /**
     * @var boolean If true, after validate success, the from and until timestamps will be assigned to the model 
	 * using fromDateAttribute and untilDateAttribute names.
	 * If false, just will work as validator
     */	
	public $setAttributes = true;

    /**
     * @inheritdoc
     */	
	public function validateAttribute($model, $attribute)
    {		
		$formatter = Yii::$app->formatter;
		
		$dates = explode($this->separator, $model->$attribute);
		
		if (count($dates) != 2) {
			return $this->addError($model, $attribute, Yii::t('daterangevalidator', 'Invalid date format. From and until values cannot be splited'));			
		} else {
			list($fromDate, $untilDate) = $dates;
		}
		
		$validationModel = DynamicModel::validateData(
			[
				$this->fromDateAttribute => $fromDate,
				$this->untilDateAttribute => $untilDate
			], 
			[
				[$this->fromDateAttribute, 'date', 'format' => $this->format, 'timestampAttribute' => $this->fromDateAttribute],
				[$this->untilDateAttribute, 'date', 'format' => $this->format, 'timestampAttribute' => $this->untilDateAttribute],
			]
		);

		if ($validationModel->hasErrors()) {
			return $this->addError($model, $attribute, Yii::t('daterangevalidator', 'From or until dates are in invalid date format'));		
		}
		
		if ($this->setAttributes === true) {		
			
			if ($model->canSetProperty($this->fromDateAttribute) === false) {
				throw new InvalidConfigException(Yii::t('daterangevalidator', 'The fromDateAttribute value set is no an attribute name of the model'));
			}
			
			if ($model->canSetProperty($this->untilDateAttribute) === false) {
				throw new InvalidConfigException(Yii::t('daterangevalidator', 'The untilDateAttribute value set is no an attribute name of the model'));
			}			
			
			if ($this->type == DateValidator::TYPE_DATE) {
				$dateTime = new \DateTime();
				$dateTime->setTimezone(new \DateTimeZone($formatter->timeZone));		
				
				$beginFromDay = clone $dateTime;
				$beginFromDay->setTimestamp($validationModel->{$this->fromDateAttribute});
				$beginFromDay->modify('today');
				
				$endUntilDay = clone $dateTime;
				$endUntilDay->setTimestamp($validationModel->{$this->untilDateAttribute});
				$endUntilDay->modify('tomorrow');
				$endUntilDay->modify('1 second ago');

				$model->{$this->fromDateAttribute} = $beginFromDay->format($this->dbFormat);
				$model->{$this->untilDateAttribute} = $endUntilDay->format($this->dbFormat);			
			} elseif ($this->type == DateValidator::TYPE_DATETIME) {
				$model->{$this->fromDateAttribute} = $validationModel->{$this->fromDateAttribute};
				$model->{$this->untilDateAttribute} = $validationModel->{$this->untilDateAttribute};				
			} else {
				throw new InvalidConfigException(Yii::t('daterangevalidator', 'Invalid date type. Only date and datetime types allowed'));
			}			
		}
    }
}
