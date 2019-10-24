import { ModalDirective } from 'angular-bootstrap-md';
import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';

@Component({
    selector: 'app-confirm-model',
    templateUrl: './confirm-model.component.html',
    styleUrls: ['./confirm-model.component.scss']
})
export class ConfirmModelComponent implements OnInit {
    @ViewChild('frame') modal: ModalDirective;
    @ViewChild('adminframe') adminmodal: ModalDirective;
    @ViewChild('otheruserframe') otherusermodal: ModalDirective;

    private routerSLParam: string;
    private routerNSLParam: string;
    private routerAdminParam: string;
    private routerOtherUserParam: string;
    private paramiter: Array<any>;
    constructor(
        private router: Router
    ) { }

    ngOnInit() {
    }

    onShow(routerSlParam: string, routerNSLParam: string, paramiter: Array<any>): void {
        this.routerSLParam =  routerSlParam;
        this.routerNSLParam =  routerNSLParam;
        this.paramiter = paramiter;
        this.modal.show();
    }

    onShowAdmin(routerAdminParam: string, paramiter: Array<any>): void {
        this.routerAdminParam =  routerAdminParam;
        this.paramiter = paramiter;
        this.adminmodal.show();
    }

    sriLankan(): void {
        this.router.navigate([this.routerSLParam, { request: this.paramiter['sparam'] }]);
        this.modal.hide();
    }

    nonSriLankan() {
        this.router.navigate([this.routerNSLParam, { request: this.paramiter['nsparam'] }]);
        this.modal.hide();
    }

    companyAdmin() {
        this.router.navigate([this.routerAdminParam, { request: this.paramiter['adminparam'] }]);
        this.adminmodal.hide();
    }

    companyAdminNSL() {
        this.router.navigate(['/non-srilankan-company-admin/register', { request: this.paramiter['adminparam'] }]);
        this.adminmodal.hide();
    }


    onShowOtherStakeholder(routerOtherUserParam: string, paramiter: Array<any>): void {
        this.routerOtherUserParam =  routerOtherUserParam;
        this.paramiter = paramiter;
        this.otherusermodal.show();
    }

    companyOtherUserSL() {
        this.router.navigate([this.routerOtherUserParam, { request: this.paramiter['adminparam'] }]);
        this.otherusermodal.hide();
    }
    companyOtherUserNONSL() {
        this.router.navigate(['/non-srilankan-other-user/register', { request: this.paramiter['adminparam'] }]);
        this.otherusermodal.hide();
    }

}
