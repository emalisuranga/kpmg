import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameCancelBoxComponent } from './name-cancel-box.component';

describe('NameCancelBoxComponent', () => {
  let component: NameCancelBoxComponent;
  let fixture: ComponentFixture<NameCancelBoxComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameCancelBoxComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameCancelBoxComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
