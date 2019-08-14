<?php
/**
 * @file
 * Object analysis functions for kreXX
 * kreXX: Krumo eXXtended
 *
 * This is a debugging tool, which displays structured information
 * about any PHP object. It is a nice replacement for print_r() or var_dump()
 * which are used by a lot of PHP developers.
 * @author brainworXX GmbH <info@brainworxx.de>
 *
 * kreXX is a fork of Krumo, which was originally written by:
 * Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @license http://opensource.org/licenses/LGPL-2.1 GNU Lesser General Public License Version 2.1
 * @package Krexx
 */

namespace Krexx;

/**
 * This class hosts the object analysis functions.
 *
 * @package Krexx
 */
class Objects {

  /**
   * Render a dump for an object.
   *
   * @param mixed $data
   *   The object we want to analyse.
   * @param string $name
   *   The name of the object.
   *
   * @return string
   *   The generated markup.
   */
  public Static Function analyseObject($data, $name) {
    static $level = 0;

    $output = '';
    $parameter = array($data, $name);
    $level++;

    if (Hive::isInHive($data)) {
      // Tell them, we've been here before
      // but also say who we are.
      $output .= Render::renderRecursion($name, get_class($data), Toolbox::generateDomIdFromObject($data));

      // We will not render this one, but since we
      // return to wherever we came from, we need to decrese the level.
      $level--;
      return $output;
    }
    // Remember, that we've been here before.
    Hive::addToHive($data);

    $anon_function = function (&$parameter) {
      $data = $parameter[0];
      $name = $parameter[1];
      $output = '';

      // Dumping all Properties
      // But only if we have any.
      if (count(get_object_vars($data))) {
        $parameter = array($data);
        $anon_function = function (&$parameter) {
          $data = $parameter[0];
          // Standard dump of the vars.
          return Internals::interateThrough($data);
        };
        $output .= Render::renderExpandableChild($name, 'class internals', $anon_function, $parameter, 'Public properties');
      }

      // Dumping all protected properties.
      if (Config::getConfigValue('deep', 'analyseProtected') == 'true') {
        $ref = new \ReflectionClass($data);
        $ref_props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);
        if (count($ref_props)) {
          $output .= Objects::getReflectionPropertiesData($ref_props, $name, $ref, $data, 'Protected properties');
        }
      }

      // Dumping all private properties.
      if (Config::getConfigValue('deep', 'analysePrivate') == 'true') {
        $ref = new \ReflectionClass($data);
        $ref_props = $ref->getProperties(\ReflectionProperty::IS_PRIVATE);
        if (count($ref_props)) {
          $output .= Objects::getReflectionPropertiesData($ref_props, $name, $ref, $data, 'Private properties');
        }
      }

      // Dumping all methods
      // but only if we have any.
      $output .= Objects::getMethodData($data, $name);

      // Dumping traversable data.
      if (Config::getConfigValue('deep', 'analyseTraversable') == 'true') {
        $output .= Objects::getTraversableData($data, $name);
      }

      // Dumping all configured debug functions.
      $output .= Objects::pollAllConfiguredDebugMethods($data, $name);
      return $output;
    };


    // Output data from the class.
    $output .= Render::renderExpandableChild($name, 'class', $anon_function, $parameter, get_class($data), Toolbox::generateDomIdFromObject($data));
    // We've finished this one, and can decrease the levelsetting.
    $level--;
    return $output;
  }

  /**
   * Gets the properties from a reflection property of the object.
   *
   * @param array $ref_props
   *   The list of the reflection properties.
   * @param string $name
   *   The name of the object we are analysing.
   * @param \ReflectionClass $ref
   *   The reflection of the object we are currently analysing.
   * @param object $data
   *   The object we are currently analysing.
   * @param string $label
   *   The additional part of the template file.
   *
   * @return string
   *   The generated markup.
   */
  public static function getReflectionPropertiesData(array $ref_props, $name, \ReflectionClass $ref, $data, $label) {
    // I need to preprocess them, since I do not want to render a
    // reflection property.
    $default = $ref->getDefaultProperties();
    $private_props = array();
    foreach ($ref_props as $ref_property) {
      $ref_property->setAccessible(TRUE);
      $value = $ref_property->getValue($data);
      $prop_name = $ref_property->name;
      if (is_null($value)) {
        // No Value is set?
        // We might want to look at the default value.
        $value = $default[$prop_name];
      }
      $private_props[$prop_name] = $value;
    }

    $parameter = array($private_props);
    $anon_function = function (&$parameter) {
      $data = $parameter[0];
      // Standard dump of the vars.
      return Internals::interateThrough($data);
    };
    return Render::renderExpandableChild($name, 'class internals', $anon_function, $parameter, $label);
  }

  /**
   * Dumps all infos about the public methods of an object.
   *
   * @param object $data
   *   The object we want to analyse.
   * @param string $name
   *   The name of the object we want to analyse.
   *
   * @return string
   *   The generated markup.
   */
  public static function getMethodData($data, $name) {
    // Dumping all methods but only if we have any.
    $public = array();
    $protected = array();
    $private = array();
    $ref = new \ReflectionClass($data);
    if (Config::getConfigValue('methods', 'analysePublicMethods') == 'true') {
      $public = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
    }
    if (Config::getConfigValue('methods', 'analyseProtectedMethods') == 'true') {
      $protected = $ref->getMethods(\ReflectionMethod::IS_PROTECTED);
    }
    if (Config::getConfigValue('methods', 'analysePrivateMethods') == 'true') {
      $private = $ref->getMethods(\ReflectionMethod::IS_PRIVATE);
    }

    // Is there anything to analyse?
    $methods = array_merge($public, $protected, $private);
    if (count($methods)) {
      // We need to sort these alphabetically.
      $sorting_callback = function($a, $b) {
        return strcmp($a->name, $b->name);
      };
      usort($methods, $sorting_callback);

      $parameter = array($ref, $methods);
      $anon_function = function (&$parameter) {
        return Objects::analyseMethods($parameter[0], $parameter[1]);
      };

      return Render::renderExpandableChild($name, 'class internals', $anon_function, $parameter, 'Methods');
    }
  }

  /**
   * Dumps all available traversable data.
   *
   * @param object $data
   *   The object we are analysing.
   * @param string $name
   *   The name of the object we want to analyse.
   *
   * @return string
   *   The generated markup.
   */
  public static function getTraversableData($data, $name) {
    if (is_a($data, 'Traversable')) {
      $parameter = iterator_to_array($data);
      $anon_function = function (&$data) {
        // This could be anything, we need to examine it first.
        return Internals::analysisHub($data);
      };
      return Render::renderExpandableChild($name, 'Foreach', $anon_function, $parameter, 'Traversable Info');
    }
  }

  /**
   * Calls all configured debug methods in die class.
   *
   * I've added a try and an empty error function callback
   * to catch possible problems with this. This will,
   * of cause, not stop a possible fatal in the function
   * itself.
   *
   * @param object $data
   *   The object we are analysing.
   * @param string $name
   *   The name of the object we are analysing.
   *
   * @return string
   *   The generated markup.
   */
  public static function pollAllConfiguredDebugMethods($data, $name) {
    $output = '';

    $func_list = explode(',', Config::getConfigValue('deep', 'debugMethods'));
    foreach ($func_list as $func_name) {
      if (is_callable(array($data, $func_name))) {
        // Add a try to prevent the hosting CMS from doing something stupid.
        try {
          $args = array();
          // We need to deactivate the current error handling to
          // prevent the host system to do anything stupid.
          set_error_handler(function() {
            // Do nothing.
          });
          $parameter = $data->$func_name($args);
          // Reactivate whatever errorhandling we had previously.
          restore_error_handler();
        }
        catch (\Exception $e) {
          // Do nothing.
        }
        if (isset($parameter)) {
          $anon_function = function (&$parameter) {
            return Internals::analysisHub($parameter);
          };
          $output .= Render::renderExpandableChild($name, 'debug method', $anon_function, $parameter, $func_name . ' Info');
          unset($parameter);
        }
      }
    }
    return $output;
  }

  /**
   * Render a dump for method infos.
   *
   * @param array $data
   *   The method analysis results in an array.
   * @param string $name
   *   The name of the object.
   *
   * @return string
   *   The generated markup.
   */
  public Static Function analyseMethod(array $data, $name) {
    $parameter = array($data);
    $anon_function = function ($parameter) {
      $data = $parameter[0];
      $output = '';
      foreach ($data as $key => $string) {
        if ($key !== 'comments' && $key !== 'declared in') {
          $output .= Render::renderSingleChild($string, $key, $string, FALSE, 'reflection');
        }
        else {
          $output .= Render::renderSingleChild($string, $key, '. . .', TRUE, 'reflection');
        }
      }
      return $output;
    };
    return Render::renderExpandableChild($name, $data['declaration keywords'] . ' method', $anon_function, $parameter);
  }

  /**
   * Render a dump for the methods of an object.
   *
   * @param mixed $ref
   *   A reflection of the original class.
   * @param array $data
   *   An array with the reflection methods.
   *
   * @return string
   *   The generated markup.
   */
  public Static Function analyseMethods(\ReflectionClass $ref, $data) {
    $parameter = array($ref, $data);

    $analysis = function ($parameter) {
      $ref = $parameter[0];
      $data = $parameter[1];
      $result = '';

      // Deep analysis of the methods.
      foreach ($data as $reflection) {
        $method_data = array();
        $method = $reflection->name;
        // Get the comment from the class, it's parents, interfaces or traits.
        $method_data['comments'] = Objects::prettifyComment($reflection->getDocComment());
        $method_data['comments'] = Objects::getParentalComment($method_data['comments'], $ref, $method);
        $method_data['comments'] = Objects::getInterfaceComment($method_data['comments'], $ref, $method);
        $method_data['comments'] = Variables::encodeString($method_data['comments'], TRUE);
        // Get declaration place.
        $declaring_class = $reflection->getDeclaringClass();
        $method_data['declared in'] = htmlspecialchars($declaring_class->getFileName()) . '<br/>';
        $method_data['declared in'] .= htmlspecialchars($declaring_class->getName()) . ' ';
        $method_data['declared in'] .= 'in line ' . htmlspecialchars($reflection->getStartLine());
        // Get parameters.
        $parameters = $reflection->getParameters();
        foreach ($parameters as $parameter) {
          preg_match('/(.*)(?= \[ )/', $parameter, $key);
          $parameter = str_replace($key[0], '', $parameter);
          $method_data[$key[0]] = htmlspecialchars(trim($parameter, ' []'));
        }
        // Get visibility.
        $method_data['declaration keywords'] = '';
        if ($reflection->isPrivate()) {
          $method_data['declaration keywords'] .= ' private';
        }
        if ($reflection->isProtected()) {
          $method_data['declaration keywords'] .= ' protected';
        }
        if ($reflection->isPublic()) {
          $method_data['declaration keywords'] .= ' public';
        }
        if ($reflection->isStatic()) {
          $method_data['declaration keywords'] .= ' static';
        }
        if ($reflection->isFinal()) {
          $method_data['declaration keywords'] .= ' final';
        }
        if ($reflection->isAbstract()) {
          $method_data['declaration keywords'] .= ' abstract';
        }
        $method_data['declaration keywords'] = trim($method_data['declaration keywords']);
        $result .= Objects::analyseMethod($method_data, $method);
      }
      return $result;

    };

    return $analysis($parameter);
  }

  /**
   * Gets comments from the reflection.
   *
   * Inherited comments are resolved by recursion of this function.
   *
   * @param string $original_comment
   *   The original comment, so far. We use this function recursively,
   *   new comments are added until all of them are resolved.
   * @param \ReflectionClass $reflection
   *   The reflection class of the object we want to analyse.
   * @param string $method_name
   *   The name of the method from which we ant to get the comment.
   *
   * @return string
   *   The generated markup.
   */
  public static function getParentalComment($original_comment, \ReflectionClass $reflection, $method_name) {
    if (stripos($original_comment, '{@inheritdoc}') !== FALSE) {
      // now we need to get the parentclass and the comment
      // from the parent function
      /* @var reflectionclass $parent_class */
      $parent_class = $reflection->getParentClass();
      if (!is_object($parent_class)) {
        // we've gone too far
        // maybe a trait?
        return self::getTraitComment($original_comment, $reflection, $method_name);
      }

      try {
        $parent_method = $parent_class->getMethod($method_name);
        $parentcomment = self::prettifyComment($parent_method->getDocComment());
      }
      catch (\ReflectionException $e) {
        // Looks like we are trying to inherit from a not existing method
        // maybe a trait?
        return self::getTraitComment($original_comment, $reflection, $method_name);
      }
      // Replace it.
      $original_comment = str_ireplace('{@inheritdoc}', $parentcomment, $original_comment);
      // die(get_class($original_comment));
      // and search for further parental comments . . .
      return Objects::getParentalComment($original_comment, $parent_class, $method_name);
    }
    else {
      // We don't need to do anything with it.
      return $original_comment;
    }
  }

  /**
   * Gets the comment from all implemented interfaces.
   *
   * Iterated through an array of interfaces, to see
   * if we can resolve the inherited comment.
   *
   * @param string $original_comment
   *   The original comment, so far.
   * @param \ReflectionClass $reflection
   *   A reflection of the object we are currently analysing.
   * @param string $method_name
   *   The name of the method from which we ant to get the comment.
   *
   * @return string
   *   The generated markup.
   */
  public static function getInterfaceComment($original_comment, \ReflectionClass $reflection, $method_name) {
    if (stripos($original_comment, '{@inheritdoc}') !== FALSE) {
      $interface_array = $reflection->getInterfaces();
      foreach ($interface_array as $interface) {
        if (stripos($original_comment, '{@inheritdoc}') !== FALSE) {
          try {
            $interface_method = $interface->getMethod($method_name);
            if (!is_object($interface_method)) {
              // We've gone too far.
              // We should tell the user, that we could not resolve
              // the inherited comment.
              $original_comment = str_ireplace('{@inheritdoc}', ' ***could not resolve inherited comment*** ', $original_comment);
            }
            else {
              $interfacecomment = self::prettifyComment($interface_method->getDocComment());
              // Replace it.
              $original_comment = str_ireplace('{@inheritdoc}', $interfacecomment, $original_comment);
            }
          }
          catch (\ReflectionException $e) {
            // Method not found.
            // We should try the next interface.
          }
        }
        else {
          // Looks like we've resolved them all.
          return $original_comment;
        }
      }
      // We are still here ?!? Return the original comment.
      return $original_comment;
    }
    else {
      return $original_comment;
    }
  }

  /**
   * Gets the comment from all added traits.
   *
   * Iterated through an array of traits, to see
   * if we can resolve the inherited comment. Traits
   * are only supported since PHP 5.4, so we need to
   * check if they are available.
   *
   * @param string $original_comment
   *   The original comment, so far.
   * @param \ReflectionClass $reflection
   *   A reflection of the object we are currently analysing.
   * @param string $method_name
   *   The name of the method from which we ant to get the comment.
   *
   * @return string
   *   The generated markup.
   */
  public static function getTraitComment($original_comment, \ReflectionClass $reflection, $method_name) {
    if (stripos($original_comment, '{@inheritdoc}') !== FALSE) {
      // We need to check if we can get traits here.
      if (method_exists($reflection, 'getTraits')) {
        // Get the traits from this class.
        $trait_array = $reflection->getTraits();
        // Get the traits from the parent traits.
        foreach ($trait_array as $trait) {
          $parent_traits = $trait->getTraits();
          // Merge them into our trasit array to get al parents.
          $trait_array = array_merge($trait_array, $parent_traits);
        }
        // Now we should have an alrray with reflections of all
        // traits in the class we are currently looking at.
        foreach ($trait_array as $trait) {
          try {
            $trait_method = $trait->getMethod($method_name);
            if (!is_object($trait_method)) {
              // We've gone too far.
              // We should tell the user, that we could not resolve
              // the inherited comment.
              $original_comment = str_ireplace('{@inheritdoc}', ' ***could not resolve inherited comment*** ', $original_comment);
            }
            else {
              $trait_comment = self::prettifyComment($trait_method->getDocComment());
              // Replace it.
              $original_comment = str_ireplace('{@inheritdoc}', $trait_comment, $original_comment);
            }
          }
          catch (\ReflectionException $e) {
            // Method not found.
            // We should try the next trait.
          }
        }
        // Return what we could resolve so far.
        return $original_comment;
      }
      else {
        // Wrong PHP version. Traits are not available.
        // Maybe there is something in the interface?
        return $original_comment;;
      }
    }
    else {
      return $original_comment;
    }
  }

  /**
   * Removes the comment-chars from the comment string.
   *
   * @param string $comment
   *   The original comment from the reflection
   *   (or interface) in case if an inheritated comment.
   *
   * @return string
   *   The better readable comment
   */
  public static function prettifyComment($comment) {
    // We split our comment into sinlge lines and remove the unwanted
    // comment chars with the array_map callback.
    $comment_array = explode("\n", $comment);
    $result = array();
    foreach ($comment_array as $comment_line) {
      // We skip lines with /** and */
      if ((strpos($comment_line, '/**') === FALSE) && (strpos($comment_line, '*/') === FALSE)) {
        // Remove comment-chars, but we need to leave the whitepace intact.
        $comment_line = trim($comment_line);
        if (strpos($comment_line, '*') === 0) {
          // Remove the * by char position.
          $result[] = substr($comment_line, 1);
        }
        else {
          // We are missing the *, so we just add the line.
          $result[] = $comment_line;
        }

      }
    }

    return implode(PHP_EOL, $result);
  }
}