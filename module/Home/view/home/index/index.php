<div class="container">
    <div class="row" style="padding-bottom: 10px">
        <div class="col">
            <h2>A Multipurpose Discord Bot</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <iframe src="https://discordapp.com/widget?id=433475261661577227&theme=dark"
                    style="width: 100%; height: 100%; padding-bottom: 10px;" allowtransparency="true"
                    frameborder="0"></iframe>
        </div>
        <div class="col-sm">
            <div class="message">
                <div class="message-header">Getting Started</div>
                <div class="message-body">
                    <p>To get started, select the bot instance to login with discord and choose the guild you would like
                        to setup.</p>
                    <form method="post" action="<?= $this->url('login') ?>">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="inputGroupSelect01">Bot Name</label>
                            </div>
                            <select class="custom-select" name="clientId">
                                <?php
                                $numOptions = 0;
                                /** @var \Home\Model\TheDialgaTeam\Discord\Table\Model\DiscordAppModel $discordAppModel */
                                foreach ($this->discordAppModels as $discordAppModel):
                                    if (!empty($discordAppModel->getClientSecret())): ?>
                                        <option value="<?= $discordAppModel->getClientId() ?>"><?= $discordAppModel->getAppName() ?></option>
                                        <?php
                                        $numOptions++;
                                    endif;
                                endforeach;

                                if ($numOptions == 0)
                                    echo '<option>No bot instance available (Try again later)</option>';
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="action" value="login"/>
                        <input type="hidden" name="csrf" value="<?= $this->csrf ?>"/>
                        <button class="btn btn-primary" type="submit"
                                style="color: white" <?= $numOptions == 0 ? 'disabled' : '' ?>>Login with Discord
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>