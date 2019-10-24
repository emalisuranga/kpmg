import { Injectable } from '@angular/core';

import { mFormData } from './formData.model';
import { WorkflowService } from '../workflow/workflow.service';
import { STEPS } from '../workflow/workflow.model';

@Injectable({
    providedIn: 'root'
})
export class FormDataService {

    private data: Array<mFormData>;
    private id: string;
    private newRefNo: string;
    private resubmit: boolean;
    private resDate: string;
    // private isWorkFormValid: boolean = false;
    // private isAddressFormValid: boolean = false;

    get getChangeNameData(): any {
        return this.data;
    }

    setOldRefNumber(id: any, newid: any, resubmit: boolean, resDate: string) {
        this.id = id;
        this.newRefNo = newid;
        this.resubmit = resubmit;
        this.resDate = resDate;
    }

    get getIdNumber(): string {
        return this.id;
    }

    get getNewRefNumber(): string {
        return this.newRefNo;
    }

    get getResubmit(): boolean {
        return this.resubmit;
    }

    get getResDate(): string {
        return this.resDate;
    }
}
