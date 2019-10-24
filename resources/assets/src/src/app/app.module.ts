import { SuccessPaymentComponent } from "./components/private-layout/dashboard/main-content/payment/success-payment/success-payment.component";
import { CancelInMemoryDataServiceService } from "./storage/cancel-in-memory-data-service.service";
import { NameCancelBoxComponent } from "./components/general-components/models/name-cancel-box/name-cancel-box.component";
import { SafeUrlPipe } from "./pipe/safe-url.pipe";
import { DistrictPipe } from "./pipe/district.pipe";
// Common Module
import { BrowserModule } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { NgModule, NO_ERRORS_SCHEMA, APP_INITIALIZER } from "@angular/core";
import { HttpClientModule, HTTP_INTERCEPTORS } from "@angular/common/http";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { MDBBootstrapModule } from "angular-bootstrap-md";
import { MaterialModule } from "./material-module/material.module";
import { HttpClientInMemoryWebApiModule } from "angular-in-memory-web-api";

// ngx - alerts
import { AlertModule } from "ngx-alerts";

// Install Library module
import { NgxSpinnerModule } from "ngx-spinner";
import { ToastrModule } from "ngx-toastr";
import { AppRoutingModule } from "./app-routing.module";

// App Component
import { AppComponent } from "./app.component";
import { HeaderComponent } from "./components/main-layout/header/header.component";
import { FooterComponent } from "./components/main-layout/footer/footer.component";
import { SignInComponent } from "./components/auth-layout/sign-in/sign-in.component";
import { CredentialComponent } from "./components/auth-layout/credential/credential.component";
// App Directive
import { CompareDirective } from "./directive/validator/compare.directive";
import { UniqueEmailDirective } from "./directive/validator/unique-email.directive";

// App Guard
import { AuthGuard } from "./http/guards/auth.guard";
import { NonauthGuard } from "./http/guards/nonauth.guard";

// App Interceptor
import { ErrorInterceptor } from "./http/helpers/error.interceptor";
import { HorizontalMenuComponent } from "./components/private-layout/dashboard/horizontal-menu/horizontal-menu.component";
import { VerticalMenuComponent } from "./components/private-layout/dashboard/vertical-menu/vertical-menu.component";
import { DashboardComponent } from "./components/private-layout/dashboard/dashboard.component";
import { ReservationComponent } from "./components/private-layout/dashboard/main-content/reservation/reservation.component";
import { CompanyListComponent } from "./components/private-layout/dashboard/main-content/company-list/company-list.component";
// tslint:disable-next-line:max-line-length
import { NameWithAgreeComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-agree/name-with-agree.component";
// tslint:disable-next-line:max-line-length
import { NameWithDocumentsComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-documents/name-with-documents.component";
// tslint:disable-next-line:max-line-length
import { NameWithPaymentComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-payment/name-with-payment.component";

import { AppLoadService } from "./http/shared/app-load.service";
import { HorizontalStatusBarComponent } from "./components/private-layout/dashboard/horizontal-status-bar/horizontal-status-bar.component";
import { ConfirmModelComponent } from "./components/auth-layout/confirm-model/confirm-model.component";
import { NonSriLankanComponent } from "./components/auth-layout/sign-up/non-sri-lankan/non-sri-lankan.component";
import { SriLankanComponent } from "./components/auth-layout/sign-up/sri-lankan/sri-lankan.component";
// tslint:disable-next-line:max-line-length
import { AdvanceSearchBarComponent } from "./components/private-layout/dashboard/main-content/advance-search-bar/advance-search-bar.component";
// tslint:disable-next-line:max-line-length
import { NameWithReSubmitedComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-re-submited/name-with-re-submited.component";
import { OtherServiceComponent } from "./components/private-layout/dashboard/main-content/other-service/other-service.component";
import { IncomparationComponent } from "./components/private-layout/dashboard/main-content/incomparation/incomparation.component";
import { CompanyCardComponent } from "./components/private-layout/dashboard/main-content/company-card/company-card.component";
import { SettingComponent } from "./components/private-layout/dashboard/main-content/setting/setting.component";
// tslint:disable-next-line:max-line-length
import { ChangePasswordComponent } from "./components/private-layout/dashboard/main-content/setting/change-password/change-password.component";
import { UniquePasswordDirective } from "./directive/validator/unique-password.directive";
import { NameCheckNameWithReSubmiteComponent } from "./components/private-layout/dashboard/main-content/reservation/name-check-name-with-re-submite/name-check-name-with-re-submite.component";
import { SweetAlert2Module } from "@toverux/ngx-sweetalert2";
import { PublicLayoutComponent } from "./components/public-layout/public-layout.component";
import { SearchBarComponent } from "./components/public-layout/layouts/search-bar/search-bar.component";
import { NavMenuComponent } from "./components/public-layout/nav-menu/nav-menu.component";
// tenders
import { CreateTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/create-tender/create-tender.component";
import { EditTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/edit-tender/edit-tender.component";
import { ListUserTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/list-user-tender/list-user-tender.component";
import { ListTendersComponent } from "./components/private-layout/dashboard/main-content/tenders/public/list-tenders/list-tenders.component";
import { ApplyTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/apply-tender/apply-tender.component";
import { ResubmitTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/resubmit-tender/resubmit-tender.component";

import { HighLightDirective } from "./directive/general/high-light.directive";
import { GlobleUserService } from "./http/shared/globle.user.service";
import { ChangeProgressBarColorDirective } from "./directive/general/change-progress-bar-color.directive";
import { UserProfileComponent } from "./components/private-layout/dashboard/main-content/setting/user-profile/user-profile.component";
import { NgSelectModule, NG_SELECT_DEFAULT_CONFIG } from "@ng-select/ng-select";
import { CityPipe } from "./pipe/city.pipe";

import { RegisterSecretaryCardComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-card/register-secretary-card.component";
import { RegisterSecretaryNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-naturalp/register-secretary-naturalp.component";
import { RegisterSecretaryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-firm/register-secretary-firm.component";
import { RegisterSecretaryPvtComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-pvt/register-secretary-pvt.component";
import { MainSearchPipe } from "./pipe/main-search.pipe";

import { NgIdleKeepaliveModule } from "@ng-idle/keepalive";
import { MomentModule } from "angular2-moment";
import { SessionWarningComponent } from "./components/general-components/models/session-warning/session-warning.component";
import { SessionTimeOutComponent } from "./components/general-components/models/session-time-out/session-time-out.component";
import { AwordTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/aword-tender/aword-tender.component";
import { RegisterAuditorCardComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-card/register-auditor-card.component";
import { RegisterAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-naturalp-sl/register-auditor-naturalp-sl.component";
import { RegisterAuditorNaturalpNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-naturalp-nonsl/register-auditor-naturalp-nonsl.component";
import { RegisterAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-firm/register-auditor-firm.component";
import { PaymentComponent } from "./components/private-layout/dashboard/main-content/payment/payment.component";
import { NameValidityExtensionComponent } from "./components/general-components/models/name-validity-extension/name-validity-extension.component";

import { SelectSocietyRegistrationTypeComponent } from "./components/private-layout/dashboard/main-content/society/select-society-registration-type/select-society-registration-type.component";
import { SocietyNameReservationComponent } from "./components/private-layout/dashboard/main-content/society/society-name-reservation/society-name-reservation.component";
import { NameWithAgreeReservationComponent } from "./components/private-layout/dashboard/main-content/society/name-with-agree-reservation/name-with-agree-reservation.component";
import { SocietyIncorporationComponent } from "./components/private-layout/dashboard/main-content/society/society-incorporation/society-incorporation.component";
import { SocietyBulkComponent } from "./components/private-layout/dashboard/main-content/society-bulk/society-bulk.component";
import { NameWithReSubmiteSocietyComponent } from "./components/private-layout/dashboard/main-content/society/name-with-re-submite-society/name-with-re-submite-society.component";
import { NameCheckNameWithReSubmiteSocietyComponent } from "./components/private-layout/dashboard/main-content/society/name-check-name-with-re-submite-society/name-check-name-with-re-submite-society.component";
import { SocietyResubmitIncorporationComponent } from "./components/private-layout/dashboard/main-content/society/society-resubmit-incorporation/society-resubmit-incorporation.component";
import { GNdivisionPipe } from "./pipe/gndivision.pipe";
import { ResubmitAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-firm/resubmit-auditor-firm.component";
import { ResubmitAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-naturalp-sl/resubmit-auditor-naturalp-sl.component";
import { ResubmitAuditorNaturalpNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-naturalp-nonsl/resubmit-auditor-naturalp-nonsl.component";
import { ResubmitCommentCardSlComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-sl/resubmit-comment-card-sl.component";
import { ResubmitCommentCardFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-firm/resubmit-comment-card-firm.component";
import { ResubmitCommentCardNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-nonsl/resubmit-comment-card-nonsl.component";
import { ResubmitSecretaryNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-secretary-naturalp/resubmit-secretary-naturalp.component";
import { ResubmitSecretaryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-secretary-firm/resubmit-secretary-firm.component";
import { ResubmitCommentCardNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-comment-card-naturalp/resubmit-comment-card-naturalp.component";
import { ResubmitCommentCardSecFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-comment-card-sec-firm/resubmit-comment-card-sec-firm.component";
import { AddressChangeComponent } from "./components/private-layout/dashboard/main-content/address-change/address-change.component";
import { AddressChangeResubmitComponent } from "./components/private-layout/dashboard/main-content/address-change-resubmit/address-change-resubmit.component";

// Issue of shares
import { IssueOfSharesComponent } from "./components/private-layout/dashboard/main-content/issue-of-shares/issue-of-shares.component";
import { IssueOfSharesResubmitComponent } from "./components/private-layout/dashboard/main-content/issue-of-shares-resubmit/issue-of-shares-resubmit.component";

import { MigreteUserComponent } from "./components/auth-layout/migrete-user/migrete-user.component";
import { DirectorSecretaryChangeComponent } from "./components/private-layout/dashboard/main-content/director-secretary-change/director-secretary-change/director-secretary-change.component";

import { ApplyRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/apply-renewal/apply-renewal.component";
import { ResubmitRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/resubmit-renewal/resubmit-renewal.component";
import { RenewalAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-auditor-firm/renewal-auditor-firm.component";
import { RenewalAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-auditor-naturalp-sl/renewal-auditor-naturalp-sl.component";
import { ApplyReRegistrationComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/apply-re-registration/apply-re-registration.component";
import { ResubmitReRegistrationComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/resubmit-re-registration/resubmit-re-registration.component";
import { GetCertificatesComponent } from "./components/private-layout/dashboard/main-content/incomparation/get-certificates/get-certificates.component";
import { GetCompanyCertificatesComponent } from "./components/private-layout/dashboard/main-content/incomparation/get-company-certificates/get-company-certificates.component";
import { MigreteUserInfoComponent } from "./components/public-layout/migrete-user-info/migrete-user-info.component";
import { RenewalResubmitAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-resubmit-auditor-naturalp-sl/renewal-resubmit-auditor-naturalp-sl.component";
import { RenewalResubmitAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-resubmit-auditor-firm/renewal-resubmit-auditor-firm.component";
import { IssueOfDebenturesComponent } from "./components/private-layout/dashboard/main-content/issue-of-debentures/issue-of-debentures/issue-of-debentures.component";
import { AccountingAddressChangeComponent } from "./components/private-layout/dashboard/main-content/accounting-addresses/accounting-address-change/accounting-address-change.component";
import { AccountingAddressChangeResubmitComponent } from "./components/private-layout/dashboard/main-content/accounting-addresses/accounting-address-change-resubmit/accounting-address-change-resubmit.component";
import { AuthInterceptor } from "./http/helpers/auth.interceptor";
import { AnnualReturnComponent } from "./components/private-layout/dashboard/main-content/annual-return/annual-return.component";
import { VerifyCompanyComponent } from "./components/private-layout/dashboard/main-content/incomparation/verify-company/verify-company.component";
import { IssueOfDebenturesResubmitComponent } from "./components/private-layout/dashboard/main-content/issue-of-debentures/issue-of-debentures-resubmit/issue-of-debentures-resubmit.component";
import {
  OwlDateTimeModule,
  OWL_DATE_TIME_FORMATS,
  OwlNativeDateTimeModule
} from "ng-pick-datetime";
import { OwlMomentDateTimeModule } from "ng-pick-datetime-moment";
import { RequestSecretaryCertifiedCopiesComponent } from "./components/private-layout/dashboard/main-content/secretary/request-secretary-certified-copies/request-secretary-certified-copies.component";
import { DirectorModelComponent } from "./components/private-layout/dashboard/main-content/company-list/director-model/director-model.component";
import { ReductionStatedCapitalComponent } from "./components/private-layout/dashboard/main-content/reduction-stated-capital/reduction-stated-capital.component";
import { OnlyNumberDirective } from "./directive/validator/only-number.directive";
import { ReductionStatedCapitalUploadComponent } from "./components/private-layout/dashboard/main-content/reduction-stated-capital/reduction-stated-capital-upload/reduction-stated-capital-upload.component";
import { ReductionStatedComponent } from "./components/private-layout/dashboard/main-content/reduction-stated-capital/reduction-stated/reduction-stated.component";
import { CallOnSharesComponent } from "./components/private-layout/dashboard/main-content/call-on-shares/call-on-shares.component";
import { BalanceSheetdateComponent } from "./components/private-layout/dashboard/main-content/balance-sheet/balance-sheetdate/balance-sheetdate.component";
import { BalanceSheetdateResubmitComponent } from "./components/private-layout/dashboard/main-content/balance-sheet/balance-sheetdate-resubmit/balance-sheetdate-resubmit.component";
import { ChargesComponent } from "./components/private-layout/dashboard/main-content/charges/charges.component";
import { RecordsRegistersComponent } from "./components/private-layout/dashboard/main-content/records-registers/records-registers/records-registers.component";
import { RecordsRegistersResubmitComponent } from "./components/private-layout/dashboard/main-content/records-registers/records-registers-resubmit/records-registers-resubmit.component";
import { RegisterOfChargesComponent } from "./components/private-layout/dashboard/main-content/register-of-charges/register-of-charges.component";
import { MemoDateComponent } from "./components/private-layout/dashboard/main-content/memo-date/memo-date/memo-date.component";
import { MemoDateResubmitComponent } from "./components/private-layout/dashboard/main-content/memo-date/memo-date-resubmit/memo-date-resubmit.component";
import { NoticeOfChangeNameOfOverseasComponent } from "./components/private-layout/dashboard/main-content/notice-of-change-name-of-overseas/notice-of-change-name-of-overseas.component";
import { AppointsOfAdministratorComponent } from "./components/private-layout/dashboard/main-content/appoints-of-administrator/appoints-of-administrator.component";
import { CompanyNoticesComponent } from "./components/private-layout/dashboard/main-content/company-notices/company-notices.component";
import { AlterationsOfOverseasCompanyComponent } from "./components/private-layout/dashboard/main-content/alterations-of-overseas-company/alterations-of-overseas-company.component";
import { AlterationsOfOffshoreCompanyComponent } from "./components/private-layout/dashboard/main-content/alterations-of-offshore-company/alterations-of-offshore-company.component";
import { SharesRedemptionAcquisitionComponent } from "./components/private-layout/dashboard/main-content/shares-redemption-acquisition/shares-redemption-acquisition.component";
import { ReductionCapitalPaymentComponent } from "./components/private-layout/dashboard/main-content/reduction-capital-payment/reduction-capital-payment.component";

import { AngularEditorModule } from "@kolkov/angular-editor";
import { ProspectusForRegistrationComponent } from "./components/private-layout/dashboard/main-content/workflows/prospectus-for-registration/prospectus-for-registration.component";
import { AnnualAccountsComponent } from "./components/private-layout/dashboard/main-content/workflows/annual-accounts/annual-accounts.component";
import { CompanyAdminComponent } from "./components/auth-layout/sign-up/company-admin/company-admin.component";
import { OthersCourtOrderComponent } from "./components/private-layout/dashboard/main-content/others-court-order/others-court-order.component";
import { NonSriLankanCompanyAdminComponent } from "./components/auth-layout/sign-up/non-sri-lankan-company-admin/non-sri-lankan-company-admin.component";
import { RequestComponent } from "./components/private-layout/dashboard/main-content/correspondence/request/request.component";
import { NewCorrRequestComponent } from "./components/private-layout/dashboard/main-content/correspondence/new-corr-request/new-corr-request.component";
import { SearchCompanyForCorrComponent } from "./components/private-layout/dashboard/main-content/correspondence/search-company-for-corr/search-company-for-corr.component";
import { ListCorrComponent } from "./components/private-layout/dashboard/main-content/correspondence/list-corr/list-corr.component";
import { PriorApprovalComponent } from "./components/private-layout/dashboard/main-content/prior-approval/prior-approval.component";
import { StatedCapitalComponent } from "./components/private-layout/dashboard/main-content/stated-capital/stated-capital.component";
import { SpecialResolutionComponent } from "./components/private-layout/dashboard/main-content/special-resolution/special-resolution.component";
import { StatementOfAffairsComponent } from "./components/private-layout/dashboard/main-content/statement-of-affairs/statement-of-affairs.component";
import { ListPriorApprovalComponent } from "./components/private-layout/dashboard/main-content/prior-approval/list-prior-approval/list-prior-approval.component";
import { SriLankanOtherStakeholderComponent } from "./components/auth-layout/sign-up/sri-lankan-other-stakeholder/sri-lankan-other-stakeholder.component";
import { NonSriLankanOtherStakeholderComponent } from "./components/auth-layout/sign-up/non-sri-lankan-other-stakeholder/non-sri-lankan-other-stakeholder.component";
import { RegisterAdminOtherCompanyComponent } from "./components/private-layout/dashboard/main-content/appoints-of-administrator/register-admin-other-company/register-admin-other-company.component";
import { IssueOfSharesNewComponent } from "./components/private-layout/dashboard/main-content/issue-of-shares-new/issue-of-shares-new.component";
import { AuditorIndCardComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-ind-card/auditor-ind-card.component";
import { AuditorFirmCardComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-firm-card/auditor-firm-card.component";
import { OtherUserAttachCompaniesComponent } from "./components/private-layout/dashboard/main-content/charges/other-user-attach-companies/other-user-attach-companies.component";
import { SearchCompanyForCourtOrderComponent } from "./components/private-layout/dashboard/main-content/others-court-order/search-company-for-court-order/search-company-for-court-order.component";
import { CourtOrderListComponent } from "./components/private-layout/dashboard/main-content/others-court-order/court-order-list/court-order-list.component";
import { AppliedTendersComponent } from "./components/private-layout/dashboard/main-content/tenders/public/applied-tenders/applied-tenders.component";
import { AuditorIndChangeComponentComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-ind-change-component/auditor-ind-change-component.component";
import { CheckboxComponentComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-ind-change-component/checkbox-component/checkbox-component.component";
import { CheckboxGroupComponentComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-ind-change-component/checkbox-group-component/checkbox-group-component.component";
import { AlterationsOfSecretoryIndividualComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-individual/alterations-of-secretory-individual.component";
import { StrikeOffComponent } from "./components/private-layout/dashboard/main-content/strike-off/strike-off.component";
import { OverseasStrikeOffComponent } from "./components/private-layout/dashboard/main-content/overseas-strike-off/overseas-strike-off.component";
import { AuditorsStrikeOffComponent } from "./components/private-layout/dashboard/main-content/auditors-strike-off/auditors-strike-off.component";
import { AlterationsOfSecretoryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-firm/alterations-of-secretory-firm.component";
import { AuditorFirmChangeComponentComponentComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-firm-change-component-component/auditor-firm-change-component-component.component";
import { AlterationsOfSecretoryPvtComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-pvt/alterations-of-secretory-pvt.component";
import { SecretaryDelistingComponent } from "./components/private-layout/dashboard/main-content/secretary-delisting/secretary-delisting.component";
import { ApplyNewRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/apply-new-renewal/apply-new-renewal.component";
import { ApplyNewReregisterComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/apply-new-reregister/apply-new-reregister.component";

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
    FooterComponent,
    SignInComponent,
    CompareDirective,
    UniqueEmailDirective,
    CredentialComponent,
    SearchBarComponent,
    NavMenuComponent,
    HorizontalMenuComponent,
    VerticalMenuComponent,
    DashboardComponent,
    ReservationComponent,
    CompanyListComponent,
    NameWithAgreeComponent,
    NameWithDocumentsComponent,
    NameWithPaymentComponent,
    HorizontalStatusBarComponent,
    ConfirmModelComponent,
    NonSriLankanComponent,
    SriLankanComponent,
    AdvanceSearchBarComponent,
    NameWithReSubmitedComponent,
    OtherServiceComponent,
    IncomparationComponent,
    SettingComponent,
    CompanyCardComponent,
    ChangePasswordComponent,
    UniquePasswordDirective,
    NameCheckNameWithReSubmiteComponent,
    PublicLayoutComponent,
    MigreteUserInfoComponent,
    // tenders
    CreateTenderComponent,
    EditTenderComponent,
    ListUserTenderComponent,
    ListTendersComponent,
    HighLightDirective,
    ChangeProgressBarColorDirective,
    ApplyTenderComponent,
    ResubmitTenderComponent,
    UserProfileComponent,
    AwordTenderComponent,

    // Secretary
    RegisterSecretaryCardComponent,
    RegisterSecretaryNaturalpComponent,
    RegisterSecretaryFirmComponent,
    RegisterSecretaryPvtComponent,

    // pipe
    SafeUrlPipe,
    DistrictPipe,
    CityPipe,
    GNdivisionPipe,
    MainSearchPipe,

    SessionWarningComponent,
    SessionTimeOutComponent,
    SuccessPaymentComponent,
    RegisterAuditorCardComponent,
    RegisterAuditorNaturalpSlComponent,
    RegisterAuditorNaturalpNonslComponent,
    RegisterAuditorFirmComponent,
    PaymentComponent,
    NameCancelBoxComponent,
    NameValidityExtensionComponent,

    // Society
    SelectSocietyRegistrationTypeComponent,
    SocietyNameReservationComponent,
    NameWithAgreeReservationComponent,
    SocietyIncorporationComponent,
    SocietyBulkComponent,
    ResubmitAuditorFirmComponent,
    ResubmitAuditorNaturalpSlComponent,
    ResubmitAuditorNaturalpNonslComponent,
    ResubmitCommentCardSlComponent,
    ResubmitCommentCardFirmComponent,
    ResubmitCommentCardNonslComponent,
    NameWithReSubmiteSocietyComponent,
    NameCheckNameWithReSubmiteSocietyComponent,
    SocietyResubmitIncorporationComponent,
    ResubmitSecretaryNaturalpComponent,
    ResubmitSecretaryFirmComponent,
    ResubmitCommentCardNaturalpComponent,
    ResubmitCommentCardSecFirmComponent,
    AddressChangeComponent,
    AddressChangeResubmitComponent,

    // Issue of shares
    IssueOfSharesComponent,
    IssueOfSharesResubmitComponent,

    MigreteUserComponent,
    DirectorSecretaryChangeComponent,
    ApplyRenewalComponent,
    ResubmitRenewalComponent,
    RenewalAuditorFirmComponent,
    RenewalAuditorNaturalpSlComponent,
    ApplyReRegistrationComponent,
    ResubmitReRegistrationComponent,
    GetCertificatesComponent,
    GetCompanyCertificatesComponent,
    RenewalResubmitAuditorNaturalpSlComponent,
    RenewalResubmitAuditorFirmComponent,
    IssueOfDebenturesComponent,
    AccountingAddressChangeComponent,
    AccountingAddressChangeResubmitComponent,
    AnnualReturnComponent,
    VerifyCompanyComponent,
    IssueOfDebenturesResubmitComponent,
    RequestSecretaryCertifiedCopiesComponent,
    DirectorModelComponent,
    ReductionStatedCapitalComponent,
    OnlyNumberDirective,
    ReductionStatedCapitalUploadComponent,
    ReductionStatedComponent,
    CallOnSharesComponent,
    BalanceSheetdateComponent,
    BalanceSheetdateResubmitComponent,
    ChargesComponent,
    RecordsRegistersComponent,
    RecordsRegistersResubmitComponent,
    RegisterOfChargesComponent,
    MemoDateComponent,
    MemoDateResubmitComponent,
    NoticeOfChangeNameOfOverseasComponent,
    AppointsOfAdministratorComponent,
    CompanyNoticesComponent,
    AlterationsOfOverseasCompanyComponent,
    AlterationsOfOffshoreCompanyComponent,
    SharesRedemptionAcquisitionComponent,
    ReductionCapitalPaymentComponent,
    ProspectusForRegistrationComponent,
    AnnualAccountsComponent,
    CompanyAdminComponent,
    OthersCourtOrderComponent,
    NonSriLankanCompanyAdminComponent,
    RequestComponent,
    NewCorrRequestComponent,
    SearchCompanyForCorrComponent,
    ListCorrComponent,
    PriorApprovalComponent,
    StatedCapitalComponent,
    SpecialResolutionComponent,
    StatementOfAffairsComponent,
    ListPriorApprovalComponent,
    SriLankanOtherStakeholderComponent,
    NonSriLankanOtherStakeholderComponent,
    RegisterAdminOtherCompanyComponent,
    IssueOfSharesNewComponent,
    AuditorIndCardComponent,
    AuditorFirmCardComponent,
    OtherUserAttachCompaniesComponent,
    AppliedTendersComponent,
    AuditorIndChangeComponentComponent,
    CheckboxComponentComponent,
    CheckboxGroupComponentComponent,
    AlterationsOfSecretoryIndividualComponent,
    StrikeOffComponent,
    OverseasStrikeOffComponent,
    AuditorsStrikeOffComponent,
    SearchCompanyForCourtOrderComponent,
    CourtOrderListComponent,
    AppliedTendersComponent,
    AlterationsOfSecretoryFirmComponent,
    AuditorFirmChangeComponentComponentComponent,
    AlterationsOfSecretoryPvtComponent,
    SecretaryDelistingComponent,
    ApplyNewRenewalComponent,
    ApplyNewReregisterComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    NgSelectModule,
    MDBBootstrapModule.forRoot(),
    MaterialModule,
    AppRoutingModule,
    NgxSpinnerModule,
    OwlDateTimeModule,
    OwlMomentDateTimeModule,
    OwlNativeDateTimeModule,
    ToastrModule.forRoot(),
    SweetAlert2Module.forRoot({
      buttonsStyling: false,
      customClass: "modal-content",
      confirmButtonClass: "btn btn-primary",
      cancelButtonClass: "btn"
    }),
    MomentModule,
    NgIdleKeepaliveModule.forRoot(),
    AngularEditorModule,
    AlertModule.forRoot({ maxMessages: 2, timeout: 3000, position: "right" })
  ],
  schemas: [NO_ERRORS_SCHEMA],
  providers: [
    AppLoadService,
    GlobleUserService,
    AuthGuard,
    NonauthGuard,
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true },
    { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true },
    {
      provide: NG_SELECT_DEFAULT_CONFIG,
      useValue: {
        notFoundText: "Custom not found"
      }
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
