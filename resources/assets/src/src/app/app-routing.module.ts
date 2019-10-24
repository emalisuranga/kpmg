import { PaymentComponent } from "./components/private-layout/dashboard/main-content/payment/payment.component";
import { SuccessPaymentComponent } from "./components/private-layout/dashboard/main-content/payment/success-payment/success-payment.component";
import { UserProfileComponent } from "./components/private-layout/dashboard/main-content/setting/user-profile/user-profile.component";
import { UnAuthParamGuard } from "./http/guards/un-auth-param.guard";
import { SettingComponent } from "./components/private-layout/dashboard/main-content/setting/setting.component";
import { CompanyCardComponent } from "./components/private-layout/dashboard/main-content/company-card/company-card.component";
// tslint:disable-next-line:max-line-length
import { NameWithReSubmitedComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-re-submited/name-with-re-submited.component";
// tslint:disable-next-line:max-line-length
import { AdvanceSearchBarComponent } from "./components/private-layout/dashboard/main-content/advance-search-bar/advance-search-bar.component";
import { NonSriLankanComponent } from "./components/auth-layout/sign-up/non-sri-lankan/non-sri-lankan.component";
import { SriLankanComponent } from "./components/auth-layout/sign-up/sri-lankan/sri-lankan.component";
import { NonauthGuard } from "./http/guards/nonauth.guard";
import { CredentialComponent } from "./components/auth-layout/credential/credential.component";
import { NgModule } from "@angular/core";
import { Routes, RouterModule } from "@angular/router";
import { ReservationComponent } from "./components/private-layout/dashboard/main-content/reservation/reservation.component";
import { AuthGuard } from "./http/guards/auth.guard";
import { DashboardComponent } from "./components/private-layout/dashboard/dashboard.component";
import { CompanyListComponent } from "./components/private-layout/dashboard/main-content/company-list/company-list.component";
// tslint:disable-next-line:max-line-length
import { NameWithAgreeComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-agree/name-with-agree.component";
// tslint:disable-next-line:max-line-length
import { NameWithDocumentsComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-documents/name-with-documents.component";
// tslint:disable-next-line:max-line-length
import { NameWithPaymentComponent } from "./components/private-layout/dashboard/main-content/reservation/name-with-payment/name-with-payment.component";
// tslint:disable-next-line:max-line-length
import { ChangePasswordComponent } from "./components/private-layout/dashboard/main-content/setting/change-password/change-password.component";
import { IncomparationComponent } from "./components/private-layout/dashboard/main-content/incomparation/incomparation.component";

// tenders
import { CreateTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/create-tender/create-tender.component";
import { EditTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/edit-tender/edit-tender.component";
import { ListUserTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/list-user-tender/list-user-tender.component";
import { ListTendersComponent } from "./components/private-layout/dashboard/main-content/tenders/public/list-tenders/list-tenders.component";
import { ApplyTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/apply-tender/apply-tender.component";
import { PublicLayoutComponent } from "./components/public-layout/public-layout.component";
import { SearchBarComponent } from "./components/public-layout/layouts/search-bar/search-bar.component";
import { ResubmitTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/resubmit-tender/resubmit-tender.component";
import { AwordTenderComponent } from "./components/private-layout/dashboard/main-content/tenders/public/aword-tender/aword-tender.component";

import { RegisterSecretaryCardComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-card/register-secretary-card.component";
import { RegisterSecretaryNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-naturalp/register-secretary-naturalp.component";
import { RegisterSecretaryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-firm/register-secretary-firm.component";
import { RegisterSecretaryPvtComponent } from "./components/private-layout/dashboard/main-content/secretary/register-secretary-pvt/register-secretary-pvt.component";
import { ResubmitSecretaryNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-secretary-naturalp/resubmit-secretary-naturalp.component";
import { ResubmitSecretaryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-secretary-firm/resubmit-secretary-firm.component";
import { ResubmitCommentCardSecFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-comment-card-sec-firm/resubmit-comment-card-sec-firm.component";
import { ResubmitCommentCardNaturalpComponent } from "./components/private-layout/dashboard/main-content/secretary/resubmit-comment-card-naturalp/resubmit-comment-card-naturalp.component";
import { RequestSecretaryCertifiedCopiesComponent } from "./components/private-layout/dashboard/main-content/secretary/request-secretary-certified-copies/request-secretary-certified-copies.component";

import { RegisterAuditorCardComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-card/register-auditor-card.component";
import { RegisterAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-naturalp-sl/register-auditor-naturalp-sl.component";
import { RegisterAuditorNaturalpNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-naturalp-nonsl/register-auditor-naturalp-nonsl.component";
import { RegisterAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/register-auditor-firm/register-auditor-firm.component";
import { ResubmitAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-firm/resubmit-auditor-firm.component";
import { ResubmitAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-naturalp-sl/resubmit-auditor-naturalp-sl.component";
import { ResubmitAuditorNaturalpNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-auditor-naturalp-nonsl/resubmit-auditor-naturalp-nonsl.component";
import { ResubmitCommentCardSlComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-sl/resubmit-comment-card-sl.component";
import { ResubmitCommentCardNonslComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-nonsl/resubmit-comment-card-nonsl.component";
import { ResubmitCommentCardFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/resubmit-comment-card-firm/resubmit-comment-card-firm.component";
import { RenewalAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-auditor-naturalp-sl/renewal-auditor-naturalp-sl.component";
import { RenewalAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-auditor-firm/renewal-auditor-firm.component";
import { RenewalResubmitAuditorNaturalpSlComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-resubmit-auditor-naturalp-sl/renewal-resubmit-auditor-naturalp-sl.component";
import { RenewalResubmitAuditorFirmComponent } from "./components/private-layout/dashboard/main-content/auditor/renewal-resubmit-auditor-firm/renewal-resubmit-auditor-firm.component";

import { SelectSocietyRegistrationTypeComponent } from "./components/private-layout/dashboard/main-content/society/select-society-registration-type/select-society-registration-type.component";
import { SocietyNameReservationComponent } from "./components/private-layout/dashboard/main-content/society/society-name-reservation/society-name-reservation.component";
import { NameWithAgreeReservationComponent } from "./components/private-layout/dashboard/main-content/society/name-with-agree-reservation/name-with-agree-reservation.component";
import { SocietyIncorporationComponent } from "./components/private-layout/dashboard/main-content/society/society-incorporation/society-incorporation.component";
import { SocietyBulkComponent } from "./components/private-layout/dashboard/main-content/society-bulk/society-bulk.component";
import { NameWithReSubmiteSocietyComponent } from "./components/private-layout/dashboard/main-content/society/name-with-re-submite-society/name-with-re-submite-society.component";
import { SocietyResubmitIncorporationComponent } from "./components/private-layout/dashboard/main-content/society/society-resubmit-incorporation/society-resubmit-incorporation.component";
import { MigreteUserInfoComponent } from "./components/public-layout/migrete-user-info/migrete-user-info.component";
import { AddressChangeComponent } from "./components/private-layout/dashboard/main-content/address-change/address-change.component";
import { AddressChangeResubmitComponent } from "./components/private-layout/dashboard/main-content/address-change-resubmit/address-change-resubmit.component";
import { ApplyRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/apply-renewal/apply-renewal.component";
import { ResubmitRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/resubmit-renewal/resubmit-renewal.component";
import { ApplyReRegistrationComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/apply-re-registration/apply-re-registration.component";
import { ResubmitReRegistrationComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/resubmit-re-registration/resubmit-re-registration.component";
import { GetCertificatesComponent } from "./components/private-layout/dashboard/main-content/incomparation/get-certificates/get-certificates.component";
import { GetCompanyCertificatesComponent } from "./components/private-layout/dashboard/main-content/incomparation/get-company-certificates/get-company-certificates.component";
import { IssueOfDebenturesComponent } from "./components/private-layout/dashboard/main-content/issue-of-debentures/issue-of-debentures/issue-of-debentures.component";
import { AccountingAddressChangeComponent } from "./components/private-layout/dashboard/main-content/accounting-addresses/accounting-address-change/accounting-address-change.component";
import { AccountingAddressChangeResubmitComponent } from "./components/private-layout/dashboard/main-content/accounting-addresses/accounting-address-change-resubmit/accounting-address-change-resubmit.component";
import { AnnualReturnComponent } from "./components/private-layout/dashboard/main-content/annual-return/annual-return.component";
import { VerifyCompanyComponent } from "./components/private-layout/dashboard/main-content/incomparation/verify-company/verify-company.component";
import { IssueOfSharesComponent } from "./components/private-layout/dashboard/main-content/issue-of-shares/issue-of-shares.component";
import { IssueOfSharesResubmitComponent } from "./components/private-layout/dashboard/main-content/issue-of-shares-resubmit/issue-of-shares-resubmit.component";
import { IssueOfDebenturesResubmitComponent } from "./components/private-layout/dashboard/main-content/issue-of-debentures/issue-of-debentures-resubmit/issue-of-debentures-resubmit.component";

import { DirectorSecretaryChangeComponent } from "./components/private-layout/dashboard/main-content/director-secretary-change/director-secretary-change/director-secretary-change.component";
import { ReductionStatedCapitalComponent } from "./components/private-layout/dashboard/main-content/reduction-stated-capital/reduction-stated-capital.component";
import { ReductionStatedComponent } from "./components/private-layout/dashboard/main-content/reduction-stated-capital/reduction-stated/reduction-stated.component";
import { CallOnSharesComponent } from "./components/private-layout/dashboard/main-content/call-on-shares/call-on-shares.component";
import { BalanceSheetdateComponent } from "./components/private-layout/dashboard/main-content/balance-sheet/balance-sheetdate/balance-sheetdate.component";
import { BalanceSheetdateResubmitComponent } from "./components/private-layout/dashboard/main-content/balance-sheet/balance-sheetdate-resubmit/balance-sheetdate-resubmit.component";
import { RegisterOfChargesComponent } from "./components/private-layout/dashboard/main-content/register-of-charges/register-of-charges.component";
import { ChargesComponent } from "./components/private-layout/dashboard/main-content/charges/charges.component";

import { RecordsRegistersComponent } from "./components/private-layout/dashboard/main-content/records-registers/records-registers/records-registers.component";
import { RecordsRegistersResubmitComponent } from "./components/private-layout/dashboard/main-content/records-registers/records-registers-resubmit/records-registers-resubmit.component";
import { NoticeOfChangeNameOfOverseasComponent } from "./components/private-layout/dashboard/main-content/notice-of-change-name-of-overseas/notice-of-change-name-of-overseas.component";
import { AppointsOfAdministratorComponent } from "./components/private-layout/dashboard/main-content/appoints-of-administrator/appoints-of-administrator.component";
import { CompanyNoticesComponent } from "./components/private-layout/dashboard/main-content/company-notices/company-notices.component";
import { AlterationsOfOverseasCompanyComponent } from "./components/private-layout/dashboard/main-content/alterations-of-overseas-company/alterations-of-overseas-company.component";
import { AlterationsOfOffshoreCompanyComponent } from "./components/private-layout/dashboard/main-content/alterations-of-offshore-company/alterations-of-offshore-company.component";

import { MemoDateComponent } from "./components/private-layout/dashboard/main-content/memo-date/memo-date/memo-date.component";
import { MemoDateResubmitComponent } from "./components/private-layout/dashboard/main-content/memo-date/memo-date-resubmit/memo-date-resubmit.component";
import { SharesRedemptionAcquisitionComponent } from "./components/private-layout/dashboard/main-content/shares-redemption-acquisition/shares-redemption-acquisition.component";
import { ReductionCapitalPaymentComponent } from "./components/private-layout/dashboard/main-content/reduction-capital-payment/reduction-capital-payment.component";
import { ProspectusForRegistrationComponent } from "./components/private-layout/dashboard/main-content/workflows/prospectus-for-registration/prospectus-for-registration.component";
import { AnnualAccountsComponent } from "./components/private-layout/dashboard/main-content/workflows/annual-accounts/annual-accounts.component";
import { CompanyAdminComponent } from "./components/auth-layout/sign-up/company-admin/company-admin.component";
import { NonSriLankanCompanyAdminComponent } from "./components/auth-layout/sign-up/non-sri-lankan-company-admin/non-sri-lankan-company-admin.component";
import { RequestComponent } from "./components/private-layout/dashboard/main-content/correspondence/request/request.component";
import { NewCorrRequestComponent } from "./components/private-layout/dashboard/main-content/correspondence/new-corr-request/new-corr-request.component";
import { SearchCompanyForCorrComponent } from "./components/private-layout/dashboard/main-content/correspondence/search-company-for-corr/search-company-for-corr.component";

import { OthersCourtOrderComponent } from "./components/private-layout/dashboard/main-content/others-court-order/others-court-order.component";
import { ListCorrComponent } from "./components/private-layout/dashboard/main-content/correspondence/list-corr/list-corr.component";
import { PriorApprovalComponent } from "./components/private-layout/dashboard/main-content/prior-approval/prior-approval.component";
import { StatedCapitalComponent } from "./components/private-layout/dashboard/main-content/stated-capital/stated-capital.component";
import { StatementOfAffairsComponent } from "./components/private-layout/dashboard/main-content/statement-of-affairs/statement-of-affairs.component";
import { ListPriorApprovalComponent } from "./components/private-layout/dashboard/main-content/prior-approval/list-prior-approval/list-prior-approval.component";
import { SpecialResolutionComponent } from "./components/private-layout/dashboard/main-content/special-resolution/special-resolution.component";
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
import { AlterationsOfSecretoryIndividualComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-individual/alterations-of-secretory-individual.component";
import { StrikeOffComponent } from "./components/private-layout/dashboard/main-content/strike-off/strike-off.component";
import { OverseasStrikeOffComponent } from "./components/private-layout/dashboard/main-content/overseas-strike-off/overseas-strike-off.component";
import { AlterationsOfSecretoryFirmComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-firm/alterations-of-secretory-firm.component";
import { AuditorFirmChangeComponentComponentComponent } from "./components/private-layout/dashboard/main-content/auditor/auditor-firm-change-component-component/auditor-firm-change-component-component.component";
import { AlterationsOfSecretoryPvtComponent } from "./components/private-layout/dashboard/main-content/secretary/changes/alterations-of-secretory-pvt/alterations-of-secretory-pvt.component";
import { AuditorsStrikeOffComponent } from "./components/private-layout/dashboard/main-content/auditors-strike-off/auditors-strike-off.component";
import { SecretaryDelistingComponent } from "./components/private-layout/dashboard/main-content/secretary-delisting/secretary-delisting.component";

import { ApplyNewRenewalComponent } from "./components/private-layout/dashboard/main-content/tenders/public/renewal/apply-new-renewal/apply-new-renewal.component";
import { ApplyNewReregisterComponent } from "./components/private-layout/dashboard/main-content/tenders/public/re-registration/apply-new-reregister/apply-new-reregister.component";

const routes: Routes = [
  { path: "", redirectTo: "home/search", pathMatch: "full" },
  { path: "home", redirectTo: "home/search", pathMatch: "full" },
  {
    path: "home",
    component: PublicLayoutComponent,
    canActivate: [NonauthGuard],
    children: [
      { path: "search", component: SearchBarComponent },
      {
        path: "tenders",
        component: ListTendersComponent
      }
    ]
  },
  {
    path: "user",
    loadChildren:
      "./components/public-layout/user-activation/user-activation.module#UserActivationModule"
  },
  {
    path: "forgot",
    loadChildren:
      "./components/auth-layout/Password/password.module#PasswordModule"
  },
  {
    path: "name",
    loadChildren:
      "./components/private-layout/dashboard/main-content/name-change/name-change.module#NameChangeModule"
  },
  {
    path: "migrate/infor",
    component: MigreteUserInfoComponent,
    canActivate: [NonauthGuard]
  },
  {
    path: "srilankan/register",
    component: SriLankanComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },
  {
    path: "nonesrilankan/register",
    component: NonSriLankanComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },
  {
    path: "company-admin/register",
    component: CompanyAdminComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },
  {
    path: "non-srilankan-company-admin/register",
    component: NonSriLankanCompanyAdminComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },

  {
    path: "srilankan-other-user/register",
    component: SriLankanOtherStakeholderComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },
  {
    path: "non-srilankan-other-user/register",
    component: NonSriLankanOtherStakeholderComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Registration"
    }
  },
  {
    path: "credential",
    component: CredentialComponent,
    canActivate: [NonauthGuard],
    data: {
      breadcrumb: "Account Credential"
    }
  },
  {
    path: "reservation",
    component: ReservationComponent,
    canActivate: [AuthGuard],
    data: {
      breadcrumb: "Name Reservation"
    },
    children: [
      { path: "", component: NameWithAgreeComponent, canActivate: [AuthGuard] },
      {
        path: "documents",
        component: NameWithDocumentsComponent,
        canActivate: [AuthGuard],
        data: {
          breadcrumb: "Name Reservation Documents"
        }
      }
    ]
  },
  {
    path: "reservation/payment",
    component: NameWithPaymentComponent,
    canActivate: [AuthGuard],
    data: {
      breadcrumb: "Payment"
    }
  },
  {
    path: "success/payment",
    component: SuccessPaymentComponent,
    data: {
      breadcrumb: "Payment"
    }
  },
  {
    path: "payment",
    component: PaymentComponent,
    canActivate: [AuthGuard],
    data: {
      breadcrumb: "Payment"
    }
  },
  {
    path: "dashboard",
    component: DashboardComponent,
    data: {
      breadcrumb: "Dashboard"
    },
    children: [
      {
        path: "home",
        component: CompanyListComponent,
        canActivate: [AuthGuard],
        data: {
          breadcrumb: "My Company"
        }
      },
      {
        path: "advance/name/reservation",
        component: AdvanceSearchBarComponent,
        canActivate: [AuthGuard],
        data: {
          breadcrumb: "My Company"
        }
      },
      {
        path: "name/re-submition/:id",
        component: NameWithReSubmitedComponent,
        canActivate: [AuthGuard, UnAuthParamGuard]
      },
      {
        path: "home/company/card/:id",
        component: CompanyCardComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "reduction/capital",
        component: ReductionStatedCapitalComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "reduction/capital/stated",
        component: ReductionStatedComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "settings",
        component: SettingComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "settings/change/my/password",
        component: ChangePasswordComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "settings/change/my/profile",
        component: UserProfileComponent,
        canActivate: [AuthGuard]
      },
      /*******tender routing by Udara Madushan */
      {
        path: "tenders/list",
        canActivate: [AuthGuard],
        component: ListUserTenderComponent
      },
      {
        path: "tenders/create-tender",
        canActivate: [AuthGuard],
        component: CreateTenderComponent
      },
      {
        path: "tenders/edit-tender/:tenderId",
        canActivate: [AuthGuard],
        component: EditTenderComponent
      },
      {
        path: "tenders",
        canActivate: [AuthGuard],
        component: CreateTenderComponent
      },
      /*-------------------ravihansa------------------*/
      {
        path: "selectregistersecretary",
        component: RegisterSecretaryCardComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "secretaryresubmitcomments/:id",
        component: ResubmitCommentCardNaturalpComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "secretaryresubmitcommentsfirm/:id",
        component: ResubmitCommentCardSecFirmComponent,
        canActivate: [AuthGuard]
      },

      {
        path: "secretary/alterations/:secretaryId",
        component: AlterationsOfSecretoryIndividualComponent,
        canActivate: [AuthGuard]
      },

      {
        path: "secretary-firm/alterations/:secretaryId",
        component: AlterationsOfSecretoryFirmComponent,
        canActivate: [AuthGuard]
      },

      {
        path: "secretary-pvt/alterations/:secretaryId",
        component: AlterationsOfSecretoryPvtComponent,
        canActivate: [AuthGuard]
      },

      {
        path: "selectregisterauditor",
        component: RegisterAuditorCardComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "auditorresubmitcommentssl/:id",
        component: ResubmitCommentCardSlComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "auditorresubmitcommentsnonsl/:id",
        component: ResubmitCommentCardNonslComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "auditorresubmitcommentsfirm/:id",
        component: ResubmitCommentCardFirmComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "selectauditorindcard/:id",
        component: AuditorIndCardComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "selectauditorfirmcard/:id",
        component: AuditorFirmCardComponent,
        canActivate: [AuthGuard]
      },
      /*-------------------ravihansa------------------*/

      /*-------------------thilan------------------*/
      {
        path: "selectregistersociety",
        component: SelectSocietyRegistrationTypeComponent,
        canActivate: [AuthGuard]
      },

      {
        path: "selectregistersociety/namereservation",
        component: SocietyNameReservationComponent,
        canActivate: [AuthGuard]
      },
      {
        path: "selectregistersociety/namewithresubmit",
        component: NameWithReSubmiteSocietyComponent,
        canActivate: [AuthGuard]
      }
      /*-------------------thilan------------------*/
    ]
  },

  {
    path: "dashboard/tenders-all",
    component: ListTendersComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "dashboard/tenders-applied",
    component: AppliedTendersComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "home/tenders/apply/:tenderId",
    component: ApplyTenderComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/edit/:tenderId/:applicationId",
    component: ApplyTenderComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/resubmit/:tenderId/:token",
    component: ResubmitTenderComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/awarding/:tenderId/:token",
    component: AwordTenderComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/renewal/apply/:tenderId/:token",
    component: ApplyRenewalComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/renewal/resubmit/:tenderId/:token",
    component: ResubmitRenewalComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/re-register/apply/:tenderId/:token",
    component: ApplyReRegistrationComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "home/tenders/re-register/resubmit/:tenderId/:token",
    component: ResubmitReRegistrationComponent,
    canActivate: [AuthGuard]
  },

  {
    path:
      "home/tenders/renewal/apply/new/:tender_id/:application_id/:item_id/:type",
    component: ApplyNewRenewalComponent,
    canActivate: [AuthGuard]
  },

  {
    path:
      "home/tenders/re-register/apply/new/:tender_id/:application_id/:item_id/:type",
    component: ApplyNewReregisterComponent,
    canActivate: [AuthGuard]
  },

  /********end Tender routing by Udara Madushan */
  {
    path: "dashboard/incorporation/:companyId",
    canActivate: [AuthGuard],
    component: IncomparationComponent
  },

  {
    path: "dashboard/get-certificates",
    canActivate: [AuthGuard],
    component: GetCertificatesComponent
  },
  {
    path: "dashboard/get-certificates/:companyId",
    canActivate: [AuthGuard],
    component: GetCompanyCertificatesComponent
  },
  {
    path: "home/company/verify/:certificateNo",
    // canActivate: [AuthGuard],
    component: VerifyCompanyComponent
  },

  {
    path: "dashboard/annual-return/:companyId",
    canActivate: [AuthGuard],
    component: AnnualReturnComponent
  },

  {
    path: "dashboard/register-of-charges/:companyId",
    canActivate: [AuthGuard],
    component: RegisterOfChargesComponent
  },
  {
    path: "dashboard/calls-on-shares/:companyId",
    canActivate: [AuthGuard],
    component: CallOnSharesComponent
  },

  {
    path:
      "dashboard/notice-of-name-change-of-overseas-company/:companyId/:changeId",
    canActivate: [AuthGuard],
    component: NoticeOfChangeNameOfOverseasComponent
  },
  {
    path: "dashboard/charges-registration/:companyId",
    canActivate: [AuthGuard],
    component: ChargesComponent
  },

  {
    path: "dashboard/join-as-other-user-with-other-companies",
    canActivate: [AuthGuard],
    component: OtherUserAttachCompaniesComponent
  },

  {
    path: "dashboard/appointment-of-administrator/:companyId",
    canActivate: [AuthGuard],
    component: AppointsOfAdministratorComponent
  },

  {
    path: "dashboard/company-notice/:companyId",
    canActivate: [AuthGuard],
    component: CompanyNoticesComponent
  },

  {
    path: "dashboard/alterations-of-overseas-company/:companyId",
    canActivate: [AuthGuard],
    component: AlterationsOfOverseasCompanyComponent
  },

  {
    path: "dashboard/alterations-of-offshore-company/:companyId",
    canActivate: [AuthGuard],
    component: AlterationsOfOffshoreCompanyComponent
  },
  {
    path: "dashboard/shares-redemption-acquisition/:companyId",
    canActivate: [AuthGuard],
    component: SharesRedemptionAcquisitionComponent
  },

  {
    path: "dashboard/prospectus-registration/:companyId",
    canActivate: [AuthGuard],
    component: ProspectusForRegistrationComponent
  },

  {
    path: "dashboard/annual-accounts/:companyId",
    canActivate: [AuthGuard],
    component: AnnualAccountsComponent
  },

  {
    path: "dashboard/correspondence/:companyId",
    canActivate: [AuthGuard],
    component: NewCorrRequestComponent
  },
  {
    path: "dashboard/correspondence/:companyId/:requestId",
    canActivate: [AuthGuard],
    component: RequestComponent
  },

  {
    path: "dashboard/correspondence-search-companies",
    canActivate: [AuthGuard],
    component: SearchCompanyForCorrComponent
  },

  {
    path: "dashboard/correspondence-list",
    canActivate: [AuthGuard],
    component: ListCorrComponent
  },

  {
    path: "dashboard/reduction-of-capital/:companyId",
    canActivate: [AuthGuard],
    component: StatedCapitalComponent
  },

  {
    path: "dashboard/special-resolution/:companyId",
    canActivate: [AuthGuard],
    component: SpecialResolutionComponent
  },

  {
    path: "dashboard/join-as-admin-with-other-companies",
    canActivate: [AuthGuard],
    component: RegisterAdminOtherCompanyComponent
  },

  {
    path: "dashboard/issue-of-shares/:companyId",
    component: IssueOfSharesNewComponent,
    canActivate: [AuthGuard]
  },

  /*-------------------ravihansa------------------*/
  {
    path: "dashboard/selectregistersecretary/registersecretarynatural/:nic",
    canActivate: [AuthGuard],
    component: RegisterSecretaryNaturalpComponent
  },
  {
    path: "dashboard/selectregistersecretary/registersecretaryfirm",
    canActivate: [AuthGuard],
    component: RegisterSecretaryFirmComponent
  },
  {
    path: "dashboard/selectregistersecretary/registersecretarypvt",
    canActivate: [AuthGuard],
    component: RegisterSecretaryPvtComponent
  },
  {
    path: "dashboard/selectregistersecretary/resubmitsecretarynatural/:nic",
    canActivate: [AuthGuard],
    component: ResubmitSecretaryNaturalpComponent
  },
  {
    path: "dashboard/selectregistersecretary/resubmitsecretaryfirm",
    canActivate: [AuthGuard],
    component: ResubmitSecretaryFirmComponent
  },
  {
    path: "dashboard/selectregisterauditor/registerauditornaturalsl/:nic",
    canActivate: [AuthGuard],
    component: RegisterAuditorNaturalpSlComponent
  },
  {
    path:
      "dashboard/selectregisterauditor/registerauditornaturalnonsl/:passport",
    canActivate: [AuthGuard],
    component: RegisterAuditorNaturalpNonslComponent
  },
  {
    path: "dashboard/selectregisterauditor/registerauditorfirm",
    canActivate: [AuthGuard],
    component: RegisterAuditorFirmComponent
  },
  {
    path: "dashboard/selectregisterauditor/resubmitauditorfirm",
    canActivate: [AuthGuard],
    component: ResubmitAuditorFirmComponent
  },
  {
    path: "dashboard/selectregisterauditor/resubmitauditornaturalsl/:nic",
    canActivate: [AuthGuard],
    component: ResubmitAuditorNaturalpSlComponent
  },
  {
    path:
      "dashboard/selectregisterauditor/resubmitauditornaturalnonsl/:passport",
    canActivate: [AuthGuard],
    component: ResubmitAuditorNaturalpNonslComponent
  },
  {
    path: "dashboard/renewalauditornaturalpsl/:token",
    canActivate: [AuthGuard],
    component: RenewalAuditorNaturalpSlComponent
  },
  {
    path: "dashboard/renewalauditorfirm/:token",
    canActivate: [AuthGuard],
    component: RenewalAuditorFirmComponent
  },
  {
    path: "dashboard/renewalresubmitauditornaturalpsl/:token",
    canActivate: [AuthGuard],
    component: RenewalResubmitAuditorNaturalpSlComponent
  },
  {
    path: "dashboard/renewalresubmitauditorfirm/:token",
    canActivate: [AuthGuard],
    component: RenewalResubmitAuditorFirmComponent
  },
  {
    path: "dashboard/directorsecretarychange/:companyId",
    canActivate: [AuthGuard],
    component: DirectorSecretaryChangeComponent
  },
  {
    path: "dashboard/requestsecretarycertifiedcopies/:id",
    component: RequestSecretaryCertifiedCopiesComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "dashboard/auditorchange/:audId",
    component: AuditorIndChangeComponentComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "dashboard/auditorfirmchange/:audId",
    component: AuditorFirmChangeComponentComponentComponent,
    canActivate: [AuthGuard]
  },
  /*-------------------ravihansa------------------*/

  /*-------------------thilan------------------*/
  {
    path: "namewithagreesociety",
    component: NameWithAgreeReservationComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "dashboard/societyincorporation",
    canActivate: [AuthGuard],
    component: SocietyIncorporationComponent
  },
  {
    path: "dashboard/society/bulk",
    canActivate: [AuthGuard],
    component: SocietyBulkComponent
  },
  {
    path: "dashboard/societyresubmitincorporation",
    canActivate: [AuthGuard],
    component: SocietyResubmitIncorporationComponent
  },
  {
    path: "dashboard/companyaddresschange",
    canActivate: [AuthGuard],
    component: AddressChangeComponent
  },
  {
    path: "dashboard/companyaddresschangeresubmit",
    canActivate: [AuthGuard],
    component: AddressChangeResubmitComponent
  },
  {
    path: "dashboard/companyaccountingaddresschange",
    canActivate: [AuthGuard],
    component: AccountingAddressChangeComponent
  },
  {
    path: "dashboard/companyaccountingaddresschangeresubmit",
    canActivate: [AuthGuard],
    component: AccountingAddressChangeResubmitComponent
  },
  {
    path: "dashboard/companybdchange",
    canActivate: [AuthGuard],
    component: BalanceSheetdateComponent
  },
  {
    path: "dashboard/companybdchangeresubmit",
    canActivate: [AuthGuard],
    component: BalanceSheetdateResubmitComponent
  },
  {
    path: "dashboard/companyrrchange",
    canActivate: [AuthGuard],
    component: RecordsRegistersComponent
  },
  {
    path: "dashboard/companyrrchangeresubmit",
    canActivate: [AuthGuard],
    component: RecordsRegistersResubmitComponent
  },
  {
    path: "dashboard/companymemo/:companyId",
    canActivate: [AuthGuard],
    component: MemoDateComponent
  },
  {
    path: "dashboard/companymemoresubmit/:companyId",
    canActivate: [AuthGuard],
    component: MemoDateResubmitComponent
  },
  /*-------------------thilan------------------*/

  /*-------------------Issue of shares - heshan------------------*/
  {
    path: "dashboard/shares",
    component: IssueOfSharesComponent,
    canActivate: [AuthGuard]
  },
  {
    path: "dashboard/sharesresubmit",
    component: IssueOfSharesResubmitComponent,
    canActivate: [AuthGuard]
  },
  /*-------------------Issue of shares - heshan------------------*/
  /*-------------------issue of debenture------------------*/
  {
    path: "dashboard/issueofdebentures",
    canActivate: [AuthGuard],
    component: IssueOfDebenturesComponent
  },
  {
    path: "dashboard/issueofdebenturesresubmit",
    canActivate: [AuthGuard],
    component: IssueOfDebenturesResubmitComponent
  },

  /*-------------------issue of debenture------------------*/

  {
    path: "reduction-capital/payment",
    component: ReductionCapitalPaymentComponent,
    canActivate: [AuthGuard]
  },

  /*-------------------Others Court Order------------------*/

  //  {
  //    path: 'dashboard/othersCourtOrder',
  //    component: OthersCourtOrderComponent,
  //    canActivate: [AuthGuard],
  // },

  {
    path: "dashboard/othersCourtOrder/:companyId/:status",
    component: OthersCourtOrderComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "dashboard/othersCourtOrderList/:companyId/:requestId",
    component: OthersCourtOrderComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "dashboard/othersCourtOrder-list",
    component: CourtOrderListComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "dashboard/othersCourtOrder-search",
    component: SearchCompanyForCourtOrderComponent,
    canActivate: [AuthGuard]
  },

  /*-------------------Request for prior approval------------------*/

  {
    path: "dashboard/priorApproval/:companyId",
    component: PriorApprovalComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "dashboard/ListApproval/:companyId",
    component: ListPriorApprovalComponent,
    canActivate: [AuthGuard]
  },

  {
    path: "dashboard/priorApproval/:companyId/:requestId",
    canActivate: [AuthGuard],
    component: PriorApprovalComponent
  },

  /*-------------------Statement Of Affairs------------------*/

  {
    path: "dashboard/Affairs/:companyId",
    component: StatementOfAffairsComponent,
    canActivate: [AuthGuard]
  },

  /*-------------------Strike Off------------------*/

  {
    path: "dashboard/StrikeOff/:companyId",
    component: StrikeOffComponent,
    canActivate: [AuthGuard]
  },

  /*------------------- Overseas Strike Off------------------*/

  {
    path: "dashboard/OverseasStrikeOff/:companyId",
    component: OverseasStrikeOffComponent,
    canActivate: [AuthGuard]
  },

  /*------------------- Auditor Strike Off------------------*/

  {
    path: "dashboard/AuditorStrikeOff/:auditorId/:auditorType",
    component: AuditorsStrikeOffComponent,
    canActivate: [AuthGuard]
  },

  /*------------------- secretary delisting ------------------*/

  {
    path: "dashboard/secretary-delisting/:secretaryId/:secretaryType",
    component: SecretaryDelistingComponent,
    canActivate: [AuthGuard]
  },

  { path: "**", redirectTo: "home", pathMatch: "full" }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}
