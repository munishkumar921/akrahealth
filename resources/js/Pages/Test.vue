<script setup>
import { ref, reactive, onMounted, watch } from "vue";
const CANVAS = { width: 900, height: 600 };
const BG_PATH = "/assets/images/illustrations/m/ABDOMINAL_INFANT.jpg";
const stageRef = ref(null);
const transformerRef = ref(null);
const bgImage = ref(null);
const mode = ref("select");

const brush = reactive({
  color: "#000000",
  width: 4,
});
const fill = ref("transparent");
const lines = ref([]);
const rects = ref([]);
const circles = ref([]);
const texts = ref([]);

const drawing = reactive({
  isDown: false,
  startX: 0,
  startY: 0,
  tempId: null,
});

const selectedId = ref(null);
let lc = 1, rc = 1, cc = 1, tc = 1;

/* undo stack (store {type:'line'|'rect'|'circle'|'text', id}) */
const history = ref([]);
onMounted(() => {
  if (BG_PATH) {
    const img = new window.Image();
    img.src = BG_PATH;
    img.onload = () => (bgImage.value = img);
  }
  updateTransformer();
});

const setMode = (m) => (mode.value = m);
const getSelected = () => {
  if (!selectedId.value) return null;
  const id = selectedId.value;
  let item = lines.value.find(x => x.id === id);
  if (item) return { type: "line", item };
  item = rects.value.find(x => x.id === id);
  if (item) return { type: "rect", item };
  item = circles.value.find(x => x.id === id);
  if (item) return { type: "circle", item };
  item = texts.value.find(x => x.id === id);
  if (item) return { type: "text", item };
  return null;
};

const undo = () => {
  const last = history.value.pop();
  if (!last) return;
  if (last.type === "line") lines.value = lines.value.filter(x => x.id !== last.id);
  if (last.type === "rect") rects.value = rects.value.filter(x => x.id !== last.id);
  if (last.type === "circle") circles.value = circles.value.filter(x => x.id !== last.id);
  if (last.type === "text") texts.value = texts.value.filter(x => x.id !== last.id);
  if (selectedId.value === last.id) selectedId.value = null;
  updateTransformer();
};

const selectShape = (id, e) => {
  e.cancelBubble = true;
  selectedId.value = id;
  updateTransformer();
};
const deselect = () => {
  selectedId.value = null;
  updateTransformer();
};
const updateTransformer = () => {
  if (!transformerRef.value || !stageRef.value) return;
  const tr = transformerRef.value.getNode();
  const stage = stageRef.value.getNode();
  const node = selectedId.value ? stage.findOne(`#${selectedId.value}`) : null;
  tr.nodes(node ? [node] : []);
  tr.getLayer() && tr.getLayer().batchDraw();
};

const handleTextDblClick = (t, evt) => {
  evt.cancelBubble = true;
  const stage = stageRef.value.getNode();
  const pos = stage.getPointerPosition();

  const ta = document.createElement("textarea");
  document.body.appendChild(ta);
  ta.value = t.text || "";
  ta.style.position = "absolute";
  ta.style.left = `${stage.container().offsetLeft + pos.x}px`;
  ta.style.top = `${stage.container().offsetTop + pos.y}px`;
  ta.style.fontSize = `${t.fontSize || 20}px`;
  ta.style.padding = "2px 4px";
  ta.style.border = "1px solid #ccc";
  ta.style.zIndex = "9999";
  ta.focus();

  const commit = () => { t.text = ta.value || t.text; document.body.removeChild(ta); };
  ta.addEventListener("keydown", (e) => {
    if (e.key === "Enter") { e.preventDefault(); commit(); }
    if (e.key === "Escape") { document.body.removeChild(ta); }
  });
  ta.addEventListener("blur", commit);
};

watch(() => brush.color, (val) => {
  const sel = getSelected();
  if (!sel) return;
  if (sel.type === "line") sel.item.stroke = val;
  if (sel.type === "rect" || sel.type === "circle") sel.item.stroke = val;
  if (sel.type === "text") sel.item.fill = val;
});

watch(() => brush.width, (val) => {
  const sel = getSelected();
  if (!sel) return;
  if (sel.type === "line" || sel.type === "rect" || sel.type === "circle") {
    sel.item.strokeWidth = val;
  }
});

watch(fill, (val) => {
  const sel = getSelected();
  if (!sel) return;
  if (sel.type === "rect" || sel.type === "circle") {
    sel.item.fill = val === "transparent" ? undefined : val;
  }
});

const onMouseDown = () => {
  const stage = stageRef.value.getNode();
  const pos = stage.getPointerPosition();
  if (!pos) return;

  if (mode.value === "select") { deselect(); return; }

  if (mode.value === "draw" || mode.value === "erase") {
    drawing.isDown = true;
    drawing.tempId = `line_${lc++}`;
    const isErase = mode.value === "erase";
    lines.value.push({
      id: drawing.tempId,
      points: [pos.x, pos.y],
      stroke: brush.color,
      strokeWidth: brush.width,
      lineCap: "round",
      lineJoin: "round",
      globalCompositeOperation: isErase ? "destination-out" : "source-over",
      draggable: false,
    });
    return;
  }

  if (mode.value === "rect") {
    drawing.isDown = true;
    drawing.startX = pos.x;
    drawing.startY = pos.y;
    drawing.tempId = `rect_${rc++}`;
    rects.value.push({
      id: drawing.tempId,
      x: pos.x,
      y: pos.y,
      width: 1,
      height: 1,
      stroke: brush.color,
      strokeWidth: brush.width,
      fill: fill.value === "transparent" ? undefined : fill.value,
      draggable: true,
    });
    return;
  }

  if (mode.value === "circle") {
    drawing.isDown = true;
    drawing.startX = pos.x;
    drawing.startY = pos.y;
    drawing.tempId = `circle_${cc++}`;
    circles.value.push({
      id: drawing.tempId,
      x: pos.x,
      y: pos.y,
      radius: 1,
      stroke: brush.color,
      strokeWidth: brush.width,
      fill: fill.value === "transparent" ? undefined : fill.value,
      draggable: true,
    });
    return;
  }

  if (mode.value === "text") {
    const id = `text_${tc++}`;
    const node = {
      id,
      text: "Text",
      x: pos.x,
      y: pos.y,
      fontSize: 20,
      fill: brush.color,
      draggable: true,
    };
    texts.value.push(node);
    history.value.push({ type: "text", id });
    selectedId.value = id;
    updateTransformer();
  }
};

const onMouseMove = () => {
  if (!drawing.isDown) return;
  const stage = stageRef.value.getNode();
  const pos = stage.getPointerPosition();
  if (!pos) return;

  if (mode.value === "draw" || mode.value === "erase") {
    const line = lines.value.find(l => l.id === drawing.tempId);
    if (!line) return;
    line.points = line.points.concat([pos.x, pos.y]);
    return;
  }

  if (mode.value === "rect") {
    const r = rects.value.find(x => x.id === drawing.tempId);
    if (!r) return;
    r.width = Math.max(1, pos.x - drawing.startX);
    r.height = Math.max(1, pos.y - drawing.startY);
    return;
  }

  if (mode.value === "circle") {
    const c = circles.value.find(x => x.id === drawing.tempId);
    if (!c) return;
    const dx = pos.x - drawing.startX;
    const dy = pos.y - drawing.startY;
    c.radius = Math.max(1, Math.sqrt(dx * dx + dy * dy));
    return;
  }
};

const onMouseUp = () => {
  if (!drawing.isDown) return;
  drawing.isDown = false;

  if (mode.value === "draw" || mode.value === "erase") {
    history.value.push({ type: "line", id: drawing.tempId });
  } else if (mode.value === "rect") {
    history.value.push({ type: "rect", id: drawing.tempId });
  } else if (mode.value === "circle") {
    history.value.push({ type: "circle", id: drawing.tempId });
  }

  drawing.tempId = null;
};
</script>

<template>
  <div style="padding:12px">
    <!-- Toolbar -->
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px; flex-wrap:wrap">
      <button @click="undo" title="Undo">↩️</button>
      <button @click="setMode('select')" :class="{ active: mode === 'select' }" title="Select">🖱️</button>
      <button @click="setMode('draw')" :class="{ active: mode === 'draw' }" title="Pen">✏️</button>
      <button @click="setMode('erase')" :class="{ active: mode === 'erase' }" title="Eraser">🩹</button>
      <button @click="setMode('text')" :class="{ active: mode === 'text' }" title="Text">T</button>
      <button @click="setMode('rect')" :class="{ active: mode === 'rect' }" title="Rectangle">▭</button>
      <button @click="setMode('circle')" :class="{ active: mode === 'circle' }" title="Circle">◯</button>
      <div style="width:10px"></div>
    </div>

    <!-- Canvas -->
    <v-stage ref="stageRef" :config="{ width: CANVAS.width, height: CANVAS.height }" @mousedown="onMouseDown"
      @mousemove="onMouseMove" @mouseup="onMouseUp" style="border:1px solid #cbd5e1; background:#fff">
      <!-- Background layer -->
      <v-layer listening="false">
        <v-image v-if="bgImage" :config="{ image: bgImage, x: 0, y: 0, width: CANVAS.width, height: CANVAS.height }" />
      </v-layer>

      <!-- Shapes layer (rect/circle/text) -->
      <v-layer>
        <v-rect v-for="r in rects" :key="r.id" :id="r.id" :config="r" @mousedown="selectShape(r.id, $event)"
          @touchstart="selectShape(r.id, $event)" />
        <v-circle v-for="c in circles" :key="c.id" :id="c.id" :config="c" @mousedown="selectShape(c.id, $event)"
          @touchstart="selectShape(c.id, $event)" />
        <v-text v-for="t in texts" :key="t.id" :id="t.id" :config="t" @mousedown="selectShape(t.id, $event)"
          @touchstart="selectShape(t.id, $event)" @dblclick="handleTextDblClick(t, $event)" />
        <v-transformer ref="transformerRef" />
      </v-layer>

      <!-- Draw layer (freehand + eraser) -->
      <v-layer>
        <v-line v-for="ln in lines" :key="ln.id" :id="ln.id" :config="ln" @mousedown="selectShape(ln.id, $event)"
          @touchstart="selectShape(ln.id, $event)" />
      </v-layer>
    </v-stage>
  </div>
</template>

<style scoped>
button {
  padding: 6px 10px;
  border: 1px solid #b6c2cf;
  border-radius: 8px;
  background: #19a7ff;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

button:hover {
  filter: brightness(0.95);
}

button.active {
  outline: 2px solid #065eae;
}

select,
input[type="color"] {
  padding: 4px;
  border: 1px solid #b6c2cf;
  border-radius: 6px;
}
</style>
