<div id="main-modal" class="modal welcome-process fade in modal-manager" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>

            <form class="wizard-container" method="POST" action="#" id="js-wizard-form">
                <div class="modal-body">
                    <div class="card card-1">
                        <div class="card-body">
                            <h4 class="modal-title" style="font-weight: bold;text-transform: uppercase;font-size: 24px;">Welcome</h4>
                            <p class="m-0" style="font-size: 14px;color: red;">
                                We are scanning your site. In less than a minute, you can begin to:
                            </p>
                            <hr style="width: 100%;" class="m-t-5">

                            <div class="tab-content">

                                <div class="tab-pane active" id="tab1">
                                    <h4 class="text-center" style="">Build Your Reputation as an Expert In Your Niche</h4>
                                    <div class="row text-center">
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal1-1.jpg') }}"/>
                                                <p>Earn positive reviews from your best patients.</p>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal1-2.jpg') }}"/>
                                                <p>Get compelling content designed to position you as an expert.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr style="width: 100%;">
                                </div>

                                <div class="tab-pane" id="tab2" style="display: none;">
                                    <h4 class="text-center">
                                        Differentiate Your Practice From the Competition
                                    </h4>
                                    <div class="row text-center">
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal2-1.jpg') }}"/>
                                                <p>
                                                    Access pre-written content that defines your brand and what makes
                                                    you unique.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal2-2.jpg') }}"/>
                                                <p>
                                                    Focus on a targeted niche market that’s interested in what you
                                                    offer.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr style="width: 100%;">
                                </div>

                                <div class="tab-pane" id="tab3" style="display: none;">
                                    <h4 class="text-center">
                                        Turn Your Niche Into a Major Profit Center
                                    </h4>
                                    <div class="row text-center">
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal3-1.jpg') }}"/>
                                                <p>
                                                    Book more niche related procedures
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="welcome-model-box">
                                                <img src="{{ asset('public/images/modal3-2.jpg') }}"/>
                                                <p>
                                                    Become the Go-To-Expert in Your Community
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr style="width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="progress-section">
                        <div class="progress" id="js-progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                 aria-valuemax="100" style="width: 40%;">
                                <span class="progress-val">40%</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <h5 class="progress-text account">Setting up your account</h5>
                            </div>
                            <div class="col-sm-4">
                                <h5 class="progress-text collect-data">Getting data from internet</h5>
                                <img class="process-loader" src="{{ asset('public/images/blue-spinner.gif') }}"
                                     style="display: none;"/>
                            </div>
                            <div class="col-sm-4">
                                <h5 class="progress-text collect-reviews">Getting all your reviews</h5>
                                <img class="process-loader" src="{{ asset('public/images/blue-spinner.gif') }}"
                                     style="display: none;"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
