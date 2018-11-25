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
	const StringKey = 'varchar(191)';
	const Char = 'char(1)';
	const Boolean = 'tinyint(1)';
	const Integer = 'int(11)';
	const Float = 'float';
	const Date = 'date';
	const Time = 'time';
	const DateTime = 'datetime';
	const Timestamp = 'timestamp';
	const Text = 'mediumtext';
	const LongText = 'longtext';
	const Enumeration = 'enum';
	const UID = 'varchar(4)';
	const JSON = 'json';

	protected static $supportedChoices = [
		self::String => self::String,
		self::StringKey => self::StringKey,
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
		self::JSON => self::JSON
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::String => 'Tekst (1 zin)',
		self::StringKey => 'Tekst (1 zin) gebruikt als primary/foreign/unqiue key',
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
		self::JSON => 'JSON'
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::String => 's',
		self::StringKey => 'k',
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
		self::JSON => 'j'
	];
}
