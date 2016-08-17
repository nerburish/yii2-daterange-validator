Yii2-daterange-validator
=========================
Daterange validator that allows to validate if a model attribute is a valid date range format. 
Specially usefull if you want to use a DateRangePicker widget and you want to filter by from/until values

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist nerburish/yii2-daterange-validator "*"
```

or add

```
"nerburish/yii2-daterange-validator": "*"
```

to the require section of your `composer.json` file.


Exemple: Filter an attribute model by date range
-----

Imagine we have a model with a 'created_at' attribute, and we want to filter that timestamp in our grid by a date range.
In our grid we have implemented a field or widget like kartik DateRangePicker(see http://demos.krajee.com/date-range) that fills the 'created_at' input with a date range format
Ex: 12/08/2014 to 17/08/2015

We need to go to the search model and add the rule to the model.
Then, we must to add the $fromDate and $untilDate attributes to the model;

	use nerburish\daterangevalidator\DateRangeValidator;

	public $fromDate;

	public $untilDate;

	public function rules()
	{
		return [
			[['created_at'], DateRangeValidator::className()]
		];
	}
	
If the created_at value passed to the search model is a valid format range, $fromDate and $untilDate will be assgined with the respective timestamps
Then, we could add to the query the next condition:

	$query->andFilterWhere(['between', 'created_at', $this->fromDate, $this->untilDate]);	

In case we want to use other attribute names instead defaults, we should pass the attribute names that will be used like this:

	use nerburish\daterangevalidator\DateRangeValidator;

	public $myInitialDateName;

	public $myUntilDateName;

	public function rules()
	{
		return [
			[['created_at'], DateRangeValidator::className(), 'fromDateAttribute' => 'myInitialDateName', 'untilDateAttribute' => 'myUntilDateName']
		];
	}
	
By default, it's expected a date format, and this format is taken from the Yii formatter component, but we can define other formats via parameters.
Since only date is passed, automatically the from timestamp is set as 00:00:00, and until timestamp is set as 23:59:59.
Also, we can change the separator character. Ex: 12-08-2014 / 17-08-2015

	public function rules()
	{
		return [
			[['created_at'], DateRangeValidator::className(), 'format' => 'php:d-m-Y', 'separator' => ' / ', ]
		];
	}
	
However, if we want to directly pass a datetime instead a date format, then we should configure the validator like this:

	public function rules()
	{
		return [
			[['created_at'], DateRangeValidator::className(), 'type' => 'datetime', 'format' => 'php:d/m/Y H:i:s']
		];
	}

In case we just want validate that the range is a valid range and not assign the from 
and until timestamp to the attibute model, we can configure the validator of this manner:

	public function rules()
	{
		return [
			[['created_at'], DateRangeValidator::className(), 'setAttributes' => false]
		];
	}
	
