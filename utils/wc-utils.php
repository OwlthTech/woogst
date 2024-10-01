<?php

if (!function_exists('validate_gst_number')) {
      /**
       * Validates gst number regex pattern
       * @param mixed $gst_number
       * @return string
       */
      function validate_gst_number($gst_number)
      {
          // Add your logic to validate GST number format or other rules
          return preg_match('/^[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst_number);
      }
  }