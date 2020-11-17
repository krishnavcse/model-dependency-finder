<?php
namespace Krishnavcse\ModelDependencyFinder\Traits;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Schema;
use ReflectionClass;

trait DependencyFinder
{
  public function getDirContents($dir, &$results = []) {
    $attributeNames = ['department_id', 'department'];
    $files = [];

    // $dir ='/dir';
  // $dirs = glob($dir . '/Model*', GLOB_ONLYDIR);
  // dd($dirs);
  while($dirs = glob($dir . '/Model*', GLOB_ONLYDIR)) {
    $dir .= '/*';
    $results = !$results ? $dirs : array_merge($results, $dirs);
  }

  foreach ($results as $pathDirectory) {
    $directory = new RecursiveDirectoryIterator($pathDirectory);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    foreach ($regex as $file) {
      $path = str_replace(base_path(), '',$file[0]) ;
      $path = str_replace('/', '\\', $path);
      $path = str_replace('\\app', '\\App', $path);
      $path = substr($path, 0, strrpos($path, '.'));
      // dump(class_parents(new $path));
      $reflection = new ReflectionClass($path);
      if (!$reflection->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
        continue;
      }
      $file = $reflection->newInstanceWithoutConstructor(); 

      $columnNames = Schema::getColumnListing($file->getTable());
      foreach ($attributeNames as $search) {
        if (array_search($search, $columnNames)) {
          array_push($files, $path);
          break;
        }
      }
    }
  }
  return $files;
}

  public function getFiles()
  {
    // dd(base_path('app'));
    // $results = [];
    $files = $this->getDirContents(base_path('app'));

    return successResponse('',$files); 

  }

}
 