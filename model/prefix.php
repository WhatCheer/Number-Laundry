<?php

	class Prefix extends Model {
		// Why yes, this is de-normalized, how sweet of you to notice.
		// It made sense for import, and faster lookups without joins for the API.
		// Please feel free to prove me wrong and send a PR :-)
		public static $_table = 'prefixes';
	}

