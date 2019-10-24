import { DataService } from './../../storage/data.service';
import { Injectable } from '@angular/core';
import { GeneralService } from '../services/general.service';

@Injectable({
  providedIn: 'root'
})
export class CalculationService {

  private values: Array<any> = [];
  private total: number = 0;
  private penalty: number = 0;
  private MaxAmount: number = 0;
  constructor(public data: DataService, private general: GeneralService) {
    this.general.getSetting('PENALTY_LIMITED_MAX_AMOUNT', 'key').subscribe(req => {
      this.MaxAmount = req;
    });
  }

  public getEachValue(keytype: string = '') {
    return Number(this.data.payment[keytype].value);
  }

  public getValue(keytype: string = '', AdditionCopy: number = 1, Penalty: number = 0, copy: boolean = false, AdditionKey: string = 'PAYMENT_ISSUING_CERTIFIED_COPY_OF_FORM', isExeedResolutin = false): number {
    AdditionCopy = Math.round(AdditionCopy);
    if (Penalty) {
      this.penalty = Penalty;
    }
    else{
      this.penalty = 0;
    }
    let total = 0;
    if (AdditionCopy !== 1) {
      if (copy) {
        total = this.getEachValue(keytype) + (AdditionCopy * this.getEachValue(AdditionKey));
      } else {
        total = this.getEachValue(keytype) * AdditionCopy;
      }
    } else {
      total = this.data.payment[keytype].value;
    }

    if (keytype === 'PAYMENT_PENALTY_LIMITED_DATE') {
     // if (total > this.MaxAmount) {
   //     total = this.MaxAmount;
    //  }
      if (isExeedResolutin){
       // total = this.MaxAmount;
     // this.penalty = isExeedResolutin;

      }else{
        total = 0;
      }

    }
    this.values.push(total);
    return total;
  }

  public getTaxPercentage(keytype: string = 'PAYMENT_GOV_VAT'): number {
    return Number(this.data.payment[keytype].value);
  }

  public getConvenienceFeePercentage(keytype: string = 'PAYMENT_CONVENIENCE_FEE'): number {
    return Number(this.data.payment[keytype].value);
  }

  public get getTaxPer(): number {
    return this.getTaxPercentage();
  }

  public get getConvenienceFeePer(): number {
    return this.getConvenienceFeePercentage();
  }

  public get getSubAmount(): number {
    this.total = 0;
    if (this.values != null && this.values.length > 0) {
      this.values.forEach(x => this.total += Number(x));
    }
    return this.total + this.penalty;
  }

  public get getTaxAmount(): number {
    return this.getSubAmount * (Number(this.getTaxPercentage()) / 100);
  }

  public get getTaxWithSubAmount(): number {
    return this.getSubAmount * ((Number(this.getTaxPercentage()) + 100) / 100);
  }

  public get getConvenienceFee(): number {
    return this.getTaxWithSubAmount * (Number(this.getConvenienceFeePercentage()) / 100);
  }

  public get getTotalAmount(): number {
    let total = this.getTaxWithSubAmount * ((Number(this.getConvenienceFeePercentage()) + 100) / 100);
    this.values = [];
    return total;
  }

}
