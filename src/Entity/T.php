<?php
namespace CsrDelft\Orm\Entity;

/**
 * T.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De mogelijke datatypes.
 */
abstract class T extends PersistentEnum {
	const String = 'varchar(255)';
	const Char = 'char(1)';
	const Boolean = 'tinyint(1)';
	const Integer = 'int(11)';
	const Float = 'float';
	const Date = 'date';
	const Time = 'time';
	const DateTime = 'datetime';
	const Timestamp = 'timestamp';
	const Text = 'text';
	const LongText = 'longtext';
	const Enumeration = 'enum';
	const UID = 'varchar(4)';

	protected static $supportedChoices = [
		self::String => self::String,
		self::Char => self::Char,
		self::Boolean => self::Boolean,
		self::Integer => self::Integer,
		self::Float => self::Float,
		self::Date => self::Date,
		self::Time => self::Time,
		self::DateTime => self::DateTime,
		self::Timestamp => self::Timestamp,
		self::Text => self::Text,
		self::LongText => self::LongText,
		self::Enumeration => self::Enumeration,
		self::UID => self::UID,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::String => 'Tekst (1 zin)',
		self::Char => 'Karakter (1 teken)',
		self::Boolean => 'Ja/Nee-waarde',
		self::Integer => 'Geheel getal',
		self::Float => 'Kommagetal',
		self::Date => 'Datum',
		self::Time => 'Tijd',
		self::DateTime => 'Datum & tijd',
		self::Timestamp => 'Tijd (getal)',
		self::Text => 'Tekst',
		self::LongText => 'Tekst (lang)',
		self::Enumeration => 'Voorgedefinieerde waarden',
		self::UID => 'Lidnummer',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::String => 's',
		self::Char => 'c',
		self::Boolean => 'b',
		self::Integer => 'i',
		self::Float => 'f',
		self::Date => 'd',
		self::Time => 't',
		self::DateTime => 'dt',
		self::Timestamp => 'ts',
		self::Text => 't',
		self::LongText => 'lt',
		self::Enumeration => 'e',
		self::UID => 'u',
	];
}
