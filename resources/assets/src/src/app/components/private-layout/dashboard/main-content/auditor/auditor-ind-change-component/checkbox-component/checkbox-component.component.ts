// import { Component, OnInit } from '@angular/core';

// @Component({
//   selector: 'app-checkbox-component',
//   templateUrl: './checkbox-component.component.html',
//   styleUrls: ['./checkbox-component.component.scss']
// })
// export class CheckboxComponentComponent implements OnInit {

//   constructor() { }

//   ngOnInit() {
//   }

// }
import {Component, OnInit, Input, Host } from '@angular/core';
import { CheckboxGroupComponentComponent } from '../checkbox-group-component/checkbox-group-component.component';

@Component({
    selector: 'app-checkbox-component',
    template: `
    <div (click)="toggleCheck()">
    <input type="checkbox" [checked]="isChecked()" />
    <ng-content></ng-content>
    </div> `
})
export class CheckboxComponentComponent implements OnInit {
    @Input() value: any;
    @Input() id: any;

    constructor(@Host() private checkboxGroup: CheckboxGroupComponentComponent) {
    }

    ngOnInit() {
         }

    toggleCheck() {
        this.checkboxGroup.addOrRemove(this.value);
    }

    isChecked() {
        return this.checkboxGroup.contains(this.value);
    }
}
