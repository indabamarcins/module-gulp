<?php
namespace MaxBucknell\Gulp\Model\Config;


use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $result = [];

        $result['tasks'] = $this->collectTasks($source);
        $result['dependencies'] = $this->collectDependencies($source);

        return $result;
    }

    public function collectTasks(\DOMDocument $source)
    {
        $result = [];

        foreach ($source->getElementsByTagName('task') as $taskNode) {
            /** @var \DOMElement $taskNode */

            $taskName = $taskNode->getAttribute('name');
            $task = [
                'subtasks' => [],
                'source' => null,
            ];

            foreach ($taskNode->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                switch ($childNode->nodeName) {
                    case 'subtasks':
                        $task['subtasks'] = \iterator_to_array($this->collectSubtasks($childNode));
                        break;
                    case 'source':
                        $task['source'] = $childNode->textContent;
                    case 'dependencies':
                    case '#text':
                    case '#comment':
                        break;
                    default:
                        throw new \Exception('Bad XML ' . $childNode->nodeName);
                        break;
                }
            }

            $result[$taskName] = $task;
        }

        return $result;
    }

    public function collectSubtasks(\DOMElement $subtasks)
    {
        foreach ($subtasks->getElementsByTagName('task') as $subtask) {
            /** @var \DOMElement $subtask */
            yield $subtask->getAttribute('name');
        }
    }

    public function collectDependencies(\DOMDocument $source)
    {
        $xpath = new \DOMXPath($source);
        $result = [];

        foreach ($xpath->query('task/dependencies/package') as $package) {
            /** @var \DOMElement $package */
            $name = $package->getAttribute('name');
            $version = $package->getAttribute('version');

            $result[$name] = $version;
        }

        return $result;
    }
}