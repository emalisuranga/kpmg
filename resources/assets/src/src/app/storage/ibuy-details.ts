import { Input, Injectable } from '@angular/core';
import { Item } from '../http/models/payment';

@Injectable({
  providedIn: 'root'
})
export class IBuyDetails {
  private module_type = null;
  private extraPayment = null;
  private module_id = null;
  private description = null;
  private item: Array<Item> = [];


  setItem(item:  Array<Item>) {
    this.item = item;
  }

  setModuleType(type: any) {
    this.module_type = type;
  }

  setModuleId(type: any) {
    this.module_id = type;
  }

  setDescription(type: any) {
    this.description = type;
  }

  setExtraPayment(type: any) {
    this.extraPayment = type;
  }

  get getModuleType(): any {
    return this.module_type;
  }

  get getModuleId(): any {
    return this.module_id;
  }

  get getDescription(): any {
    return this.description;
  }

  get getExtraPayment(): any {
    return this.extraPayment;
  }

  get getItem(): any {
    return this.item;
  }

  clean() {
    this.module_type = null;
    this.module_id = null;
    this.description = null;
    this.extraPayment = null;
  }

}
