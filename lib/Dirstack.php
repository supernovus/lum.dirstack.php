<?php

namespace Lum;

class Dirstack implements \Countable
{
  protected array $stack;

  public function __construct(string $dir="", bool $fatal=true)
  {
    if (!empty($dir))
    { // Change to an initial directory that will be the top of the stack.
      static::chdir($dir, $fatal);
    }
    $this->stack = [static::pwd($fatal)];
  }

  public static function chdir(string $dir, bool $fatal=false): bool
  {
    if (file_exists($dir) && is_dir($dir))
    {
      if (!chdir($dir))
      { // Could not change directory.
        if ($fatal)
          throw new DirstackException("Could not change to directory");
        return false;
      }
      return true;
    }
    else
    { // Does not exist.
      if ($fatal)
        throw new DirstackException("Directory does not exist");
      return false;
    }
  }

  public static function pwd(bool $fatal=false): string
  {
    $dir = getcwd();
    if (empty($dir))
    { // This shouldn't happen, but just in case, try one fallback.
      $dir = $_ENV['PWD'];
      if (empty($dir))
      { // The fallback failed too, bye!
        if ($fatal)
          throw new DirstackException("Could not determine current directory");
      }
    }
    return $dir;
  }

  public function go(string $dir, bool $fatal=false): bool
  {
    if (static::chdir($dir, $fatal))
    {
      $this->stack[] = static::pwd($fatal);
      return true;
    }
    return false;
  }

  public function back(int $levels=1, bool $fatal=false): bool
  {
    $count = count($this->stack);
    $index = $count-1;

    if ($index === 0 || $levels < 1) 
    {
      return false;
    }
    elseif ($levels > $index)
    {
      $levels = $index;
    }

    if ($levels === 1)
    { // One level is the easiest.
      array_pop($this->stack);
      $index--;
      return static::chdir($this->stack[$index], $fatal);
    }
    else
    { // A bit trickier, but still doable.
      array_splice($this->stack, ($levels*-1), $levels);
      $index -= $levels;
      return static::chdir($this->stack[$index], $fatal);
    }
  }

  public function getStack(): array
  {
    return $this->stack;
  }

  public function count(): int
  {
    return count($this->stack);
  }

} // Dirstack class

class DirstackException extends \Exception {}
