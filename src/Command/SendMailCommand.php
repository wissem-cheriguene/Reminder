<?php

namespace App\Command;

use DateTime;
use GuzzleHttp\Client;
use SendinBlue\Client\Api;
use Doctrine\DBAL\Exception;
use SendinBlue\Client\Configuration;
use App\Repository\MessageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SendMailCommand extends Command
{

  // the name of the command (the part after "bin/console")
  protected static $defaultName = 'app:send-mail';

  private $messageRepository;
  private $params;

  public function __construct(MessageRepository $messageRepository, ContainerBagInterface $params)
  {
    parent::__construct();
    $this->messageRepository = $messageRepository;
    $this->params = $params;
  }

  protected function configure()
  {
    $this
    // the short description shown while running "php bin/console list"
    ->setDescription('Send a email.')
    // the full command description shown when running the command with
    // the "--help" option
    ->setHelp('This command allows you to send a email...');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
      // Date de début et de fin à comparer aux dates de la table message
      // Retrouve les messages à envoyer en fonction de la date à laquelle 
      // le script est lancé(CRON) +/- 30 sec
      $start = new DateTime();
      $start->modify('-30 seconds');
      $end = clone $start;
      $end->modify('+60 seconds');
      $reminders = $this->messageRepository->createQueryBuilder('m')
        ->select('m')
        ->where('m.date BETWEEN :now AND :end')
        ->setParameters([
          'now' => $start,
          'end' => $end,
        ])
        ->getQuery()
        ->getResult();

      if(count($reminders) > 0) {
        foreach ($reminders as $reminder) {
          $content = $reminder->getContent();
          $name = $reminder->getName();
          $createdAt = $reminder->getCreatedAt();
          $email = $reminder->getEmail();
          
          // Configure API key authorization: api-key
          $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->params->get('api_key'));
          // dd($config);
          // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
          // $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('api-key', 'Bearer');
          // Configure API key authorization: partner-key
          $config = Configuration::getDefaultConfiguration()->setApiKey('partner-key', $this->params->get('api_key'));
          // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
          // $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('partner-key', 'Bearer');
          
          $apiInstance = new Api\TransactionalEmailsApi(
              // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
              // This is optional, `GuzzleHttp\Client` will be used as default.
              new Client(),
              $config
          );
          // dd($userDateSubmittedReminder->format('Y-m-d à H:i:s'));
          $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail(); // \SendinBlue\Client\Model\SendSmtpEmail | Values to send a transactional email
          $sendSmtpEmail['sender'] = ['id' => 1];
          $sendSmtpEmail['to'] = [['email'=> $email, 'name'=> $name]];
          $sendSmtpEmail['templateId'] = 3;
          $sendSmtpEmail['subject'] = 'Reminder';
          $sendSmtpEmail['params'] = [
            'content' => $content,
            'username' => $name,
            'date' => $createdAt->format('d/m/Y à H:i:s')
          ];      
      
          try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            $output->writeln('Mail successfully sent!');
            
            // return this if there was no problem running the command
            // (it's equivalent to returning int(0))
            // this method must return an integer number with the "exit status code"
            // of the command. You can also use these constants to make code more readable
            return Command::SUCCESS;

          } catch (Exception $e) {
              echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;
              // or return this if some error happened during the execution
              // (it's equivalent to returning int(1))
              // return Command::FAILURE;
              return Command::FAILURE;
          }

        }
      }
      else {
        $output->writeln('No mail found to be sent!');
        return Command::SUCCESS;
      }
  }
}