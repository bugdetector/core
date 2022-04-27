<?php

namespace Src\Form;

use CoreDB\Kernel\Messenger;
use Src\Controller\Profile\SessionsController;
use Src\Entity\Session;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;
use Src\Views\TextElement;

class SessionsForm extends Form
{
    public string $method = "POST";
    /** @var Session[] */
    private array $userSessions;

    public function __construct()
    {
        parent::__construct();
        $this->userSessions = \CoreDB::currentUser()->getUserSessions();
        foreach ($this->userSessions as &$session) {
            /** @var InputWidget */
            $widget = InputWidget::create("session[{$session->ID}]")
            ->setType("submit")
            ->setLabel(
                TextElement::create($session->ip_address)
                ->addAttribute("style", "width: 200px")
                ->setTagName("div")
            )
            ->setDescription(
                Translation::getTranslation("last_access") . ": " .
                date("d-m-Y H:i:s", strtotime($session->last_access))
            )->removeClass("form-control")
            ->addClass("btn btn-sm ms-4");
            if (session_id() == $session->session_key->getValue()) {
                $widget->setValue(Translation::getTranslation("current_session"))
                ->addClass("btn-success")
                ->addAttribute("disabled", "true");
                unset($session);
            } else {
                $widget->setValue(Translation::getTranslation("logout"))
                ->addClass("btn-danger logout-session");
            }
            $this->addField($widget);
        }
    }

    public function getFormId(): string
    {
        return "profile_sessions_form";
    }

    public function validate(): bool
    {
        $sessionIds = array_map(function ($session) {
            return $session->ID->getValue();
        }, $this->userSessions);
        $sessionIdToLogout = current(array_keys($this->request["session"]));
        /** @var Session */
        $session = Session::get($sessionIdToLogout);
        if (
            !in_array($sessionIdToLogout, $sessionIds) ||
            $session->session_key->getValue() == session_id()
        ) {
            $this->setError("", Translation::getTranslation("invalid_operation"));
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $sessionIdToLogout = current(array_keys($this->request["session"]));
        $session = Session::get($sessionIdToLogout);
        $session->delete();
        $this->setMessage(
            Translation::getTranslation("session_logout_success"),
            Messenger::SUCCESS
        );
        \CoreDB::goTo(SessionsController::getUrl());
    }
}
